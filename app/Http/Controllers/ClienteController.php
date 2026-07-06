<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ClienteController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $buscar = trim((string) $request->query('buscar', ''));

        return Inertia::render('Clientes/Index', [
            'clientes' => Cliente::withCount('pedidos')
                ->when($buscar !== '', fn ($q) => $q->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%");
                }))
                ->orderBy('nombre')
                ->get(),
            'buscar' => $buscar,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Clientes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Cliente::create($this->validated($request));

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente): InertiaResponse
    {
        return Inertia::render('Clientes/Show', [
            'cliente' => $cliente,
            'pedidos' => $cliente->pedidos()->with('plan')->latest('id')->get(),
        ]);
    }

    public function edit(Cliente $cliente): InertiaResponse
    {
        return Inertia::render('Clientes/Edit', ['cliente' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($this->validated($request, $cliente));

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado.');
    }

    private function validated(Request $request, ?Cliente $cliente = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:30', 'unique:clientes,telefono'.($cliente ? ','.$cliente->id : '')],
            'email' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string'],
        ]);
    }
}
