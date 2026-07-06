<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Faq;
use App\Models\NumeroTelefonico;
use App\Models\Pedido;
use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function index(): InertiaResponse
    {
        $ingresos = Pedido::whereIn('estado', ['pagado', 'numero_asignado', 'entregado'])->sum('monto');

        return Inertia::render('Dashboard', [
            'stats' => [
                'clientes' => Cliente::count(),
                'pedidosMes' => Pedido::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'ingresos' => $ingresos,
                'pendientes' => Pedido::whereIn('estado', ['iniciado', 'en_pago', 'pagado'])->count(),
            ],
            'modulos' => [
                'planes' => Plan::count(),
                'numerosDisponibles' => NumeroTelefonico::where('estado', 'disponible')->count(),
                'pedidos' => Pedido::count(),
                'clientes' => Cliente::count(),
                'faqs' => Faq::count(),
            ],
        ]);
    }
}
