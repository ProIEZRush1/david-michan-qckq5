<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ faqs: Array });
</script>

<template>
    <Head title="Preguntas frecuentes" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Preguntas frecuentes</h2>
        </template>
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Preguntas que el bot responde automáticamente por WhatsApp, sin intervención humana.</p>
                <Link :href="route('preguntas-frecuentes.create')" class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:from-violet-500 hover:to-fuchsia-500">+ Nueva pregunta</Link>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">Pregunta</th>
                            <th class="px-5 py-3">Categoría</th>
                            <th class="px-5 py-3">Palabras clave</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="faq in faqs" :key="faq.id" class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ faq.pregunta }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span v-if="faq.categoria" class="inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-600">{{ faq.categoria }}</span>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="max-w-xs truncate text-xs text-slate-400">{{ faq.palabras_clave }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span v-if="faq.activo" class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600">Activo</span>
                                <span v-else class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Inactivo</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link :href="route('preguntas-frecuentes.edit', faq.id)" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-violet-600 transition hover:bg-violet-50">Editar</Link>
                                <ConfirmDeleteButton :href="route('preguntas-frecuentes.destroy', faq.id)" texto="Se eliminará la pregunta frecuente permanentemente." />
                            </td>
                        </tr>
                        <tr v-if="!faqs.length"><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Aún no tienes preguntas frecuentes registradas.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
