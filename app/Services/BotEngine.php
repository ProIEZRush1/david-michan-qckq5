<?php

namespace App\Services;

use App\Models\BotContact;
use App\Models\Cliente;
use App\Models\Faq;
use App\Models\Pedido;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Deterministic, DB-driven Spanish WhatsApp SALES bot for David Michan (venta de líneas
 * telefónicas).
 *
 * Finite-state machine keyed on BotContact->step:
 *   new → choosing → capturing_name → confirming → done   (+ cross-cutting "human" handoff)
 *
 * Every reply is fixed copy kept in clearly-labeled private methods so it stays trivially
 * editable. No AI/LLM call anywhere — the bot only talks to the local database.
 */
class BotEngine
{
    // ---- finite-state-machine steps -------------------------------------
    private const STEP_NEW = 'new';
    private const STEP_CHOOSING = 'choosing';
    private const STEP_CAPTURING_NAME = 'capturing_name';
    private const STEP_CONFIRMING = 'confirming';
    private const STEP_DONE = 'done';
    private const STEP_HUMAN = 'human';

    public function __construct(private GatewayClient $gateway) {}

    public function handle(string $from, ?string $fromName, string $text): void
    {
        $contact = BotContact::firstOrCreate(['phone' => $from]);

        // Keep the contact's display name fresh (WhatsApp pushName) without clobbering it with null.
        if (filled($fromName) && $contact->name !== $fromName) {
            $contact->name = $fromName;
            $contact->save();
        }

        $rawText = trim($text);
        $normalized = Str::lower($rawText);

        // ESCALATION (any step): the client wants a real person → hand off and go silent.
        if ($this->wantsHuman($normalized)) {
            if ($contact->step !== self::STEP_HUMAN) {
                $this->setStep($contact, self::STEP_HUMAN);
                $this->reply($from, $this->copyHandoff());
            }

            return;
        }

        // A human has taken over this chat → the bot stays completely silent.
        if ($contact->step === self::STEP_HUMAN) {
            return;
        }

        // The literal word "menu"/"menú" resets the funnel from anywhere.
        if (in_array($normalized, ['menu', 'menú'], true)) {
            $this->setStep($contact, self::STEP_NEW);
            $this->onNew($contact, $from);

            return;
        }

        // A question matching our knowledge base is answered without disturbing the funnel.
        $faq = $this->matchFaq($normalized);
        if ($faq) {
            $this->reply($from, $faq->respuesta);

            return;
        }

        match ($contact->step) {
            self::STEP_CHOOSING => $this->onChoosing($contact, $from, $normalized),
            self::STEP_CAPTURING_NAME => $this->onCapturingName($contact, $from, $rawText),
            self::STEP_CONFIRMING => $this->onConfirming($contact, $from, $normalized),
            self::STEP_DONE => $this->onDone($from),
            default => $this->onNew($contact, $from), // STEP_NEW / first contact / unknown
        };
    }

    // ---- states ---------------------------------------------------------

    /** Greet by name and present the active plans, then wait for a choice. */
    private function onNew(BotContact $contact, string $from): void
    {
        $plans = $this->activePlans();
        if ($plans->isEmpty()) {
            $this->reply($from, $this->copyNoPlans());

            return;
        }

        $this->setStep($contact, self::STEP_CHOOSING);
        $this->reply($from, $this->copyGreeting($contact->name).$this->planList($plans).$this->copyAskChoice());
    }

    /** Match the reply to a plan (by list number or fuzzy name), then ask for the buyer's name or confirm. */
    private function onChoosing(BotContact $contact, string $from, string $text): void
    {
        $plans = $this->activePlans();
        if ($plans->isEmpty()) {
            $this->reply($from, $this->copyNoPlans());

            return;
        }

        $plan = $this->matchPlan($plans, $text);
        if (! $plan) {
            $this->reply($from, $this->copyNoMatch().$this->planList($plans).$this->copyAskChoice());

            return;
        }

        $data = $contact->data ?? [];
        $data['plan_id'] = $plan->id;
        $contact->data = $data;

        if (blank($contact->name)) {
            $this->setStep($contact, self::STEP_CAPTURING_NAME);
            $this->reply($from, $this->copyAskName($plan));

            return;
        }

        $this->createPedidoIniciado($contact, $from, $plan, $contact->name);
        $this->setStep($contact, self::STEP_CONFIRMING);
        $this->reply($from, $this->copyConfirmPrompt($plan));
    }

    /** Capture the buyer's full name (required to close the sale), then move to confirmation. */
    private function onCapturingName(BotContact $contact, string $from, string $rawText): void
    {
        $looksLikeName = mb_strlen($rawText) >= 2 && ! ctype_digit(str_replace(' ', '', $rawText));
        if (! $looksLikeName) {
            $this->reply($from, $this->copyAskNameRetry());

            return;
        }

        $contact->name = $rawText;
        $contact->save();

        $planId = $contact->data['plan_id'] ?? null;
        $plan = $planId ? Plan::where('activo', true)->find($planId) : null;

        if (! $plan) {
            // Plan was deactivated while we waited for the name — restart the funnel cleanly.
            $this->setStep($contact, self::STEP_NEW);
            $this->onNew($contact, $from);

            return;
        }

        $this->createPedidoIniciado($contact, $from, $plan, $rawText);
        $this->setStep($contact, self::STEP_CONFIRMING);
        $this->reply($from, $this->copyConfirmPrompt($plan));
    }

    /** Affirmative → send the order to payment; negative → back to choosing. */
    private function onConfirming(BotContact $contact, string $from, string $text): void
    {
        if ($this->isYes($text)) {
            $pedido = $this->pendingPedido($contact);
            $pedido?->update(['estado' => 'en_pago']);

            Cliente::updateOrCreate(
                ['telefono' => $from],
                ['nombre' => $contact->name],
            );

            $this->setStep($contact, self::STEP_DONE);
            $this->reply($from, $this->copyConfirmed($pedido?->plan));

            return;
        }

        if ($this->isNo($text)) {
            $this->setStep($contact, self::STEP_CHOOSING);
            $plans = $this->activePlans();
            $this->reply($from, $this->copyChangedMind().$this->planList($plans).$this->copyAskChoice());

            return;
        }

        // Ambiguous reply → re-ask for an explicit yes/no.
        $this->reply($from, $this->copyConfirmRetry());
    }

    /** Order already registered → polite close; "menu" (handled upstream) restarts the flow. */
    private function onDone(string $from): void
    {
        $this->reply($from, $this->copyAlreadyDone());
    }

    // ---- persistence helpers ---------------------------------------------

    private function createPedidoIniciado(BotContact $contact, string $from, Plan $plan, ?string $nombre): void
    {
        $cliente = Cliente::updateOrCreate(
            ['telefono' => $from],
            ['nombre' => $nombre],
        );

        Pedido::create([
            'bot_contact_id' => $contact->id,
            'cliente_id' => $cliente->id,
            'plan_id' => $plan->id,
            'cliente' => $nombre,
            'telefono' => $from,
            'estado' => 'iniciado',
            'monto' => $plan->precio,
        ]);
    }

    // ---- plan helpers ---------------------------------------------------

    /** @return Collection<int,Plan> */
    private function activePlans(): Collection
    {
        return Plan::where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    /** Match by 1-based list number first, then by fuzzy name (either direction). */
    private function matchPlan(Collection $plans, string $text): ?Plan
    {
        $text = trim($text);

        if ($text !== '' && ctype_digit($text)) {
            return $plans->values()->get(((int) $text) - 1);
        }

        foreach ($plans as $plan) {
            $name = Str::lower(trim($plan->nombre));
            if ($name !== '' && (Str::contains($text, $name) || Str::contains($name, $text))) {
                return $plan;
            }
        }

        return null;
    }

    private function pendingPedido(BotContact $contact): ?Pedido
    {
        $planId = $contact->data['plan_id'] ?? null;

        return $contact->pedidos()
            ->where('estado', 'iniciado')
            ->when($planId, fn ($q) => $q->where('plan_id', $planId))
            ->latest('id')
            ->first()
            ?? $contact->pedidos()->where('estado', 'iniciado')->latest('id')->first();
    }

    // ---- FAQ knowledge base ----------------------------------------------

    /** Keyword-matches inbound text against the active FAQ knowledge base. */
    private function matchFaq(string $normalized): ?Faq
    {
        if ($normalized === '') {
            return null;
        }

        return Faq::where('activo', true)
            ->orderBy('orden')
            ->get()
            ->first(function (Faq $faq) use ($normalized) {
                foreach (explode(',', $faq->palabras_clave) as $keyword) {
                    $keyword = Str::lower(trim($keyword));
                    if ($keyword !== '' && Str::contains($normalized, $keyword)) {
                        return true;
                    }
                }

                return false;
            });
    }

    // ---- copy (editable Spanish strings) --------------------------------

    private function copyGreeting(?string $name): string
    {
        $greeting = $name ? "¡Hola, {$name}! 👋" : '¡Hola! 👋';

        return $greeting." Gracias por escribir a *".config('app.name')."* 📱\n\n"
            ."Vendemos líneas telefónicas con planes de datos, minutos y SMS. Estos son nuestros planes disponibles:\n\n";
    }

    private function planList(Collection $plans): string
    {
        $lines = $plans->values()->map(function (Plan $plan, int $i) {
            $datos = $plan->datos_gb ? $plan->datos_gb.' GB de datos' : 'Datos ilimitados';
            $minutos = $plan->minutos_ilimitados ? 'Minutos ilimitados' : 'Minutos incluidos';
            $sms = $plan->sms_ilimitados ? 'SMS ilimitados' : 'SMS incluidos';

            $line = ($i + 1).'. *'.$plan->nombre.'* — '.$this->formatPrice($plan->precio)."\n"
                ."   📶 {$datos} · ⏱️ {$minutos} · ✉️ {$sms}\n"
                ."   Vigencia: {$plan->vigencia_dias} días";

            if (filled($plan->descripcion)) {
                $line .= "\n   ".$plan->descripcion;
            }

            return $line;
        });

        return $lines->implode("\n\n");
    }

    private function copyAskChoice(): string
    {
        return "\n\n¿Cuál te interesa? Respóndeme con el *número* o el *nombre* del plan. 🙂";
    }

    private function copyNoMatch(): string
    {
        return "No identifiqué ese plan. 🤔 Estos son los disponibles:\n\n";
    }

    private function copyAskName(Plan $plan): string
    {
        return '¡Perfecto! 🙌 Para continuar con tu línea *'.$plan->nombre."*, ¿cuál es tu nombre completo? 📝";
    }

    private function copyAskNameRetry(): string
    {
        return 'Necesito tu nombre completo para registrar tu línea. ¿Cómo te llamas? 🙂';
    }

    private function copyConfirmPrompt(Plan $plan): string
    {
        return '¡Excelente elección! 🙌 Elegiste *'.$plan->nombre.'* ('.$this->formatPrice($plan->precio).").\n\n"
            .'¿Confirmas tu pedido? Responde *sí* para confirmar o *no* para elegir otro plan.';
    }

    private function copyConfirmRetry(): string
    {
        return 'Para continuar, respóndeme *sí* para confirmar tu pedido o *no* para elegir otro plan. 🙂';
    }

    private function copyChangedMind(): string
    {
        return "Sin problema. 🙌 Aquí están los planes de nuevo:\n\n";
    }

    private function copyConfirmed(?Plan $plan): string
    {
        $planNombre = $plan?->nombre ?? 'tu línea';

        return "¡Listo! ✅ Registramos tu pedido de *{$planNombre}*.\n\n"
            .'En breve te compartiremos el link de pago para completarlo. En cuanto se confirme tu pago, '
            ."te asignaremos automáticamente tu nuevo número y te avisaremos por este mismo chat. 🙌\n\n"
            .'Si quieres empezar de nuevo, escribe *menu*.';
    }

    private function copyAlreadyDone(): string
    {
        return "Ya registramos tu pedido ✅. En cuanto se confirme tu pago te asignaremos tu número y te avisaremos por aquí. 🙌\n\n"
            .'Si quieres empezar de nuevo, escribe *menu*.';
    }

    private function copyNoPlans(): string
    {
        return 'Gracias por escribir a *'.config('app.name')."* 🙌 En este momento estamos actualizando nuestros planes; "
            .'un asesor te contactará en breve para ayudarte a elegir tu línea.';
    }

    private function copyHandoff(): string
    {
        return '¡Claro que sí! 🙌 Te paso con uno de nuestros asesores para que te atienda personalmente. '
            .'En breve te contactan. ¡Quedo al pendiente! 😊';
    }

    // ---- matchers (deterministic, ported from BotResponder STYLE) -------

    /** Affirmative confirmation (guards against explicit declines). Word-boundary matched so short
     *  tokens like "va"/"si" don't fire inside larger words ("nueva", "sitio"). */
    private function isYes(string $text): bool
    {
        if ($this->isNo($text)) {
            return false;
        }

        if (preg_match('/\b(s[ií]|sip|sale|va|dale|ok|okay|claro|listo|correcto|adelante|confirm\w*|acept\w*|procede)\b/u', $text)) {
            return true;
        }

        return Str::contains($text, [
            'de acuerdo', 'me late', 'por supuesto', 'está bien', 'esta bien', 'hágale', 'hagale', 'perfecto',
        ]);
    }

    /** Explicit negative / decline. Word-boundary matched so "no" never fires inside "uno"/"bueno". */
    private function isNo(string $text): bool
    {
        return (bool) preg_match('/\b(no|nel|nop|nope|todav[ií]a no|a[uú]n no|aun no|por ahora no|'
            .'ahorita no|mejor no|otro plan|otra opci[oó]n|cambiar)\b/u', $text);
    }

    /** The client wants a real person / doesn't want a bot → hand off to a human. */
    private function wantsHuman(string $text): bool
    {
        $text = ' '.trim($text).' ';

        return (bool) preg_match('/(asesor real|un asesor|una asesora|atenci[oó]n humana|'
            .'(hablar|hablo|comunicar|comunicarme|pasar|pasas?|p[aá]same|contactar|conectar|con[eé]ctame) con (un|una|alg[uú]ien|el|la)?\s*(humano|persona|asesor|asesora|agente|ejecutiv|alguien real|alguien|due[ñn]o|encargad)|'
            .'quiero (un|una|hablar con|que me atienda un|que me atienda una)?\s*(humano|persona|asesor|asesora|agente|alguien real)|'
            .'prefiero (un|una|hablar con|que me atienda)?\s*(humano|persona|asesor|asesora|agente|alguien)|'
            .'no quiero (hablar con)?\s*(un|una)?\s*(bot|ia|robot|inteligencia artificial|asistente)|'
            .'hablar con (un|una)?\s*(ia|bot|robot|inteligencia artificial)\s*no|'
            .'no me (interes|gust)\w*\s*(hablar con\s*(un|una)?\s*)?(ia|bot|robot|asistente|inteligencia artificial))/u', $text);
    }

    // ---- utilities ------------------------------------------------------

    /** Persist a step change. */
    private function setStep(BotContact $contact, string $step): void
    {
        $contact->step = $step;
        $contact->save();
    }

    /** Format a price stored in cents as a Spanish-friendly amount. */
    private function formatPrice(int $cents): string
    {
        return '$'.number_format($cents / 100, 0, '.', ',').' MXN';
    }

    /** Every outbound reply goes through the gateway. */
    private function reply(string $to, string $message): void
    {
        $this->gateway->send($to, $message);
    }
}
