<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NumeroController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

// Admin/bot panel: the root goes straight to the panel (login if needed), never a generic page.
Route::get('/', fn () => redirect()->route('dashboard'));

// Lightweight health probe the deploy pipeline hits to verify the LIVE app + database are up,
// migrations ran and the admin was seeded (users >= 1). Public on purpose.
Route::get('/health', function () {
    try {
        return response()->json(['ok' => true, 'users' => \App\Models\User::count()]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => 'db'], 503);
    }
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/conectar', [WhatsAppController::class, 'conectar'])->name('conectar');

    Route::resource('planes', PlanController::class)->except('show');
    Route::resource('inventario', NumeroController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('clientes', ClienteController::class);

    Route::resource('pedidos', PedidoController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/pedidos/{pedido}/marcar-pagado', [PedidoController::class, 'marcarPagado'])->name('pedidos.marcar-pagado');
    Route::post('/pedidos/{pedido}/marcar-entregado', [PedidoController::class, 'marcarEntregado'])->name('pedidos.marcar-entregado');
    Route::post('/pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');

    Route::resource('preguntas-frecuentes', FaqController::class)->except('show')->parameters(['preguntas-frecuentes' => 'faq']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
