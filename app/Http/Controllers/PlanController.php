<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PlanController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Planes/Index', [
            'planes' => Plan::withCount('pedidos')
                ->orderBy('orden')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Planes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['precio'] = (int) round($data['precio'] * 100);

        Plan::create($data);

        return redirect()->route('planes.index')->with('success', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan): InertiaResponse
    {
        return Inertia::render('Planes/Edit', ['plan' => $plan]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $this->validated($request);
        $data['precio'] = (int) round($data['precio'] * 100);

        $plan->update($data);

        return redirect()->route('planes.index')->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('planes.index')->with('success', 'Plan eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string'],
            'datos_gb' => ['nullable', 'integer', 'min:0'],
            'minutos_ilimitados' => ['boolean'],
            'sms_ilimitados' => ['boolean'],
            'vigencia_dias' => ['required', 'integer', 'min:1'],
            'activo' => ['boolean'],
            'orden' => ['integer', 'min:0'],
        ]);
    }
}
