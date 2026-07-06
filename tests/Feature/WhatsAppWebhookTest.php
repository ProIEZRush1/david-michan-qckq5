<?php

namespace Tests\Feature;

use App\Models\BotContact;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_requests_without_the_shared_gateway_token(): void
    {
        $response = $this->postJson('/api/wa/inbound', [
            'from' => '5215500001111',
            'text' => 'hola',
        ]);

        $response->assertStatus(401);
    }

    public function test_inbound_message_triggers_a_bot_reply_with_the_plan_catalog(): void
    {
        Http::fake();

        Plan::create([
            'nombre' => 'Plan Ligero 5GB',
            'precio' => 29900,
            'activo' => true,
        ]);

        $response = $this->postJson('/api/wa/inbound', [
            'from' => '5215500001111',
            'fromName' => 'Juana Pérez',
            'text' => 'hola',
            'isGroup' => false,
        ], [
            'x-gateway-token' => config('bot.gateway_token'),
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('bot_contacts', [
            'phone' => '5215500001111',
            'name' => 'Juana Pérez',
            'step' => 'choosing',
        ]);

        Http::assertSent(function ($request) {
            return str_contains((string) $request->url(), '/send')
                && $request['to'] === '5215500001111'
                && str_contains($request['text'], 'Plan Ligero 5GB');
        });
    }

    public function test_a_full_conversation_creates_a_pedido_and_notifies_the_customer_at_each_step(): void
    {
        Http::fake();

        $plan = Plan::create([
            'nombre' => 'Plan Plus 15GB',
            'precio' => 39900,
            'activo' => true,
        ]);

        $from = '5215500002222';
        $headers = ['x-gateway-token' => config('bot.gateway_token')];

        // 1) Greeting -> shows the catalog.
        $this->postJson('/api/wa/inbound', ['from' => $from, 'fromName' => 'Carlos Ruiz', 'text' => 'hola'], $headers)->assertOk();

        // 2) Picks the plan by number.
        $this->postJson('/api/wa/inbound', ['from' => $from, 'text' => '1'], $headers)->assertOk();

        // 3) Confirms the order.
        $this->postJson('/api/wa/inbound', ['from' => $from, 'text' => 'sí'], $headers)->assertOk();

        $this->assertDatabaseHas('pedidos', [
            'telefono' => $from,
            'plan_id' => $plan->id,
            'estado' => 'en_pago',
        ]);

        $this->assertDatabaseHas('clientes', [
            'telefono' => $from,
            'nombre' => 'Carlos Ruiz',
        ]);

        $contact = BotContact::where('phone', $from)->firstOrFail();
        $this->assertSame('done', $contact->step);

        Http::assertSentCount(3);
    }

    public function test_faq_keyword_is_answered_without_disturbing_the_active_funnel(): void
    {
        Http::fake();

        Plan::create(['nombre' => 'Plan Pro 30GB', 'precio' => 49900, 'activo' => true]);

        \App\Models\Faq::create([
            'pregunta' => '¿Qué cobertura tienen?',
            'respuesta' => 'Cobertura en toda la República Mexicana.',
            'palabras_clave' => 'cobertura,señal',
            'activo' => true,
        ]);

        $from = '5215500003333';
        $headers = ['x-gateway-token' => config('bot.gateway_token')];

        $this->postJson('/api/wa/inbound', ['from' => $from, 'text' => 'hola'], $headers)->assertOk();
        $this->postJson('/api/wa/inbound', ['from' => $from, 'text' => '¿tienen cobertura?'], $headers)->assertOk();

        Http::assertSent(fn ($request) => str_contains($request['text'] ?? '', 'Cobertura en toda la República Mexicana'));

        // The funnel state must be unchanged (still choosing a plan) after the FAQ interception.
        $this->assertDatabaseHas('bot_contacts', ['phone' => $from, 'step' => 'choosing']);
    }
}
