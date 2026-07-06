<?php

namespace App\Http\Controllers;

use App\Models\NumeroTelefonico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NumeroController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Inventario/Index', [
            'numeros' => NumeroTelefonico::with('pedido.cliente')
                ->orderByRaw("estado = 'disponible' desc")
                ->orderBy('id')
                ->get(),
            'resumen' => [
                'disponibles' => NumeroTelefonico::where('estado', 'disponible')->count(),
                'asignados' => NumeroTelefonico::where('estado', 'asignado')->count(),
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Inventario/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lada' => ['nullable', 'string', 'max:10'],
            'numeros' => ['required', 'string'],
        ]);

        $lada = $data['lada'] ?? null;
        $lineas = collect(preg_split('/[\r\n,]+/', $data['numeros']))
            ->map(fn ($n) => trim($n))
            ->filter();

        if ($lineas->isEmpty()) {
            return back()->withErrors(['numeros' => 'Agrega al menos un número.']);
        }

        foreach ($lineas as $numero) {
            NumeroTelefonico::firstOrCreate(
                ['numero' => Str::of($numero)->replace(' ', '')->value()],
                ['lada' => $lada, 'estado' => 'disponible'],
            );
        }

        return redirect()->route('inventario.index')->with('success', $lineas->count().' número(s) agregado(s) al inventario.');
    }

    public function destroy(NumeroTelefonico $numero): RedirectResponse
    {
        if ($numero->estado === 'asignado') {
            return back()->withErrors(['numero' => 'No puedes eliminar un número ya asignado a un pedido.']);
        }

        $numero->delete();

        return redirect()->route('inventario.index')->with('success', 'Número eliminado del inventario.');
    }
}
