<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FaqController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Faqs/Index', [
            'faqs' => Faq::orderBy('orden')->orderBy('id')->get(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Faqs/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));

        return redirect()->route('preguntas-frecuentes.index')->with('success', 'Pregunta frecuente creada.');
    }

    public function edit(Faq $faq): InertiaResponse
    {
        return Inertia::render('Faqs/Edit', ['faq' => $faq]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('preguntas-frecuentes.index')->with('success', 'Pregunta frecuente actualizada.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('preguntas-frecuentes.index')->with('success', 'Pregunta frecuente eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'pregunta' => ['required', 'string', 'max:255'],
            'respuesta' => ['required', 'string'],
            'palabras_clave' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'activo' => ['boolean'],
            'orden' => ['integer', 'min:0'],
        ]);
    }
}
