<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\NumeroTelefonico;
use App\Models\Pedido;
use App\Models\Plan;
use App\Services\GatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PedidoController extends Controller
{
    public function __construct(private GatewayClient $gateway) {}

    public function index(Request $request): InertiaResponse
    {
        $estado = $request->query('estado');

        return Inertia::render('Pedidos/Index', [
            'pedidos' => Pedido::with(['plan', 'cliente', 'numeroTelefonico'])
                ->when($estado, fn ($q) => $q->where('estado', $estado))
                ->latest('id')
                ->get(),
            'estado' => $estado,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Pedidos/Create', [
            'planes' => Plan::where('activo', true)->orderBy('orden')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:30'],
            'plan_id' => ['required', 'exists:planes,id'],
            'notas' => ['nullable', 'string'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        $cliente = Cliente::updateOrCreate(
            ['telefono' => $data['telefono']],
            ['nombre' => $data['nombre']],
        );

        Pedido::create([
            'cliente_id' => $cliente->id,
            'plan_id' => $plan->id,
            'cliente' => $data['nombre'],
            'telefono' => $data['telefono'],
            'estado' => 'iniciado',
            'monto' => $plan->precio,
            'notas' => $data['notas'] ?? null,
        ]);

        return redirect()->route('pedidos.index')->with('success', 'Pedido registrado correctamente.');
    }

    public function show(Pedido $pedido): InertiaResponse
    {
        return Inertia::render('Pedidos/Show', [
            'pedido' => $pedido->load(['plan', 'cliente', 'numeroTelefonico']),
            'numerosDisponibles' => NumeroTelefonico::where('estado', 'disponible')->count(),
        ]);
    }

    public function destroy(Pedido $pedido): RedirectResponse
    {
        $this->liberarNumero($pedido);
        $pedido->delete();

        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado.');
    }

    /** Admin confirms payment was received: mark paid and try to auto-assign a number. */
    public function marcarPagado(Pedido $pedido): RedirectResponse
    {
        if (in_array($pedido->estado, ['entregado', 'cancelado'], true)) {
            return back()->withErrors(['estado' => 'Este pedido ya no admite este cambio de estado.']);
        }

        $pedido->update(['estado' => 'pagado', 'pagado_at' => now()]);
        $this->notificar($pedido, '¡Pago confirmado! ✅ Gracias por tu compra. En un momento te asignamos tu número.');

        $numero = NumeroTelefonico::where('estado', 'disponible')->orderBy('id')->first();

        if ($numero) {
            $numero->update(['estado' => 'asignado', 'asignado_at' => now()]);
            $pedido->update([
                'numero_telefonico_id' => $numero->id,
                'estado' => 'numero_asignado',
                'numero_asignado_at' => now(),
            ]);
            $this->notificar($pedido, "📱 Tu nuevo número quedó asignado: *{$numero->numero}*. En breve te contactamos para la entrega/activación.");

            return redirect()->route('pedidos.show', $pedido)->with('success', 'Pago confirmado y número asignado automáticamente.');
        }

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pago confirmado. No hay números disponibles en inventario; agrega más en Inventario.');
    }

    public function marcarEntregado(Pedido $pedido): RedirectResponse
    {
        if ($pedido->estado !== 'numero_asignado') {
            return back()->withErrors(['estado' => 'El pedido debe tener un número asignado antes de marcarse como entregado.']);
        }

        $pedido->update(['estado' => 'entregado', 'entregado_at' => now()]);
        $this->notificar($pedido, '🎉 ¡Todo listo! Tu línea ya fue entregada/activada. Gracias por tu compra.');

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido marcado como entregado.');
    }

    public function cancelar(Pedido $pedido): RedirectResponse
    {
        $this->liberarNumero($pedido);
        $pedido->update(['estado' => 'cancelado']);

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido cancelado.');
    }

    private function liberarNumero(Pedido $pedido): void
    {
        if ($pedido->numero_telefonico_id) {
            $pedido->numeroTelefonico?->update(['estado' => 'disponible', 'asignado_at' => null]);
        }
    }

    private function notificar(Pedido $pedido, string $mensaje): void
    {
        if ($pedido->telefono) {
            $this->gateway->send($pedido->telefono, $mensaje);
        }
    }
}
