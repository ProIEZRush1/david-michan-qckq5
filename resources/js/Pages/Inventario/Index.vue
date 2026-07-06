<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    numeros: Array,
    resumen: Object,
});

function formatFecha(fecha) {
    if (!fecha) return '—';
    return new Date(fecha).toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Inventario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Inventario de números</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Los números de línea disponibles para asignar automáticamente cuando el bot confirma una venta.
                </p>
                <Link
                    :href="route('inventario.create')"
                    class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:from-violet-500 hover:to-fuchsia-500"
                >
                    + Agregar números
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Disponibles</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ resumen.disponibles }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Asignados</p>
                    <p class="mt-1 text-2xl font-bold text-slate-600">{{ resumen.asignados }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">Número</th>
                            <th class="px-5 py-3">Lada</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3">Asignado a</th>
                            <th class="px-5 py-3">Asignado el</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="numero in numeros" :key="numero.id" class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ numero.numero }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ numero.lada || '—' }}</td>
                            <td class="px-5 py-4">
                                <span
                                    :class="[
                                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                                        numero.estado === 'disponible'
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-500',
                                    ]"
                                >
                                    {{ numero.estado === 'disponible' ? 'Disponible' : 'Asignado' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <template v-if="numero.pedido?.cliente?.nombre">
                                    <p class="font-medium text-slate-700">{{ numero.pedido.cliente.nombre }}</p>
                                    <p class="text-xs text-slate-400">{{ numero.pedido.cliente.telefono }}</p>
                                </template>
                                <template v-else>—</template>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ formatFecha(numero.asignado_at) }}</td>
                            <td class="px-5 py-4 text-right">
                                <ConfirmDeleteButton
                                    v-if="numero.estado === 'disponible'"
                                    :href="route('inventario.destroy', numero.id)"
                                    texto="Se eliminará el número del inventario permanentemente."
                                />
                                <span v-else class="text-xs font-semibold text-slate-400">Asignado</span>
                            </td>
                        </tr>
                        <tr v-if="!numeros.length">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">
                                Aún no tienes números registrados en el inventario.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
