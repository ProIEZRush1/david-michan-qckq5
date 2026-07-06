<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    planes: Array,
});

function formatMoney(cents) {
    return '$' + Math.round(cents / 100).toLocaleString('es-MX') + ' MXN';
}
</script>

<template>
    <Head title="Planes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Planes</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Los planes de líneas telefónicas que el bot ofrece a tus clientes por WhatsApp.
                </p>
                <Link
                    :href="route('planes.create')"
                    class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:from-violet-500 hover:to-fuchsia-500"
                >
                    + Nuevo plan
                </Link>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">Plan</th>
                            <th class="px-5 py-3">Precio</th>
                            <th class="px-5 py-3">Datos</th>
                            <th class="px-5 py-3">Vigencia</th>
                            <th class="px-5 py-3">Pedidos</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="plan in planes" :key="plan.id" class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ plan.nombre }}</p>
                                <p class="text-xs text-slate-400">{{ plan.descripcion }}</p>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ formatMoney(plan.precio) }}</td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ plan.datos_gb ? plan.datos_gb + ' GB' : 'Ilimitados' }}
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ plan.vigencia_dias }} días</td>
                            <td class="px-5 py-4 text-slate-600">{{ plan.pedidos_count }}</td>
                            <td class="px-5 py-4">
                                <span
                                    :class="[
                                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                                        plan.activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500',
                                    ]"
                                >
                                    {{ plan.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="route('planes.edit', plan.id)"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-violet-600 transition hover:bg-violet-50"
                                >
                                    Editar
                                </Link>
                                <ConfirmDeleteButton
                                    :href="route('planes.destroy', plan.id)"
                                    texto="Se eliminará el plan permanentemente."
                                />
                            </td>
                        </tr>
                        <tr v-if="!planes.length">
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                                Aún no tienes planes registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
