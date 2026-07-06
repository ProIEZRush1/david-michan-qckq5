<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    cliente: Object,
    pedidos: Array,
});

const estadoClases = {
    iniciado: 'bg-slate-100 text-slate-600',
    en_pago: 'bg-amber-50 text-amber-700',
    pagado: 'bg-blue-50 text-blue-700',
    numero_asignado: 'bg-violet-50 text-violet-700',
    entregado: 'bg-emerald-50 text-emerald-700',
    cancelado: 'bg-red-50 text-red-700',
};

const estadoLabels = {
    iniciado: 'Iniciado',
    en_pago: 'En pago',
    pagado: 'Pagado',
    numero_asignado: 'Número asignado',
    entregado: 'Entregado',
    cancelado: 'Cancelado',
};

function formatMoney(cents) {
    return '$' + Math.round(cents / 100).toLocaleString('es-MX') + ' MXN';
}

function formatFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-MX');
}
</script>

<template>
    <Head :title="cliente.nombre" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">{{ cliente.nombre }}</h2>
        </template>

        <div class="mx-auto max-w-5xl space-y-6">
            <Link
                :href="route('clientes.index')"
                class="inline-flex items-center text-sm font-semibold text-violet-600 hover:text-violet-800 hover:underline"
            >
                ← Volver a clientes
            </Link>

            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-3">
                        <h3 class="text-lg font-bold text-slate-800">{{ cliente.nombre }}</h3>
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Teléfono</dt>
                                <dd class="text-slate-700">{{ cliente.telefono }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</dt>
                                <dd class="text-slate-700">{{ cliente.email || '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Notas</dt>
                                <dd class="whitespace-pre-line text-slate-700">{{ cliente.notas || '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <Link
                        :href="route('clientes.edit', cliente.id)"
                        class="shrink-0 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:from-violet-500 hover:to-fuchsia-500"
                    >
                        Editar
                    </Link>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-lg font-bold tracking-tight text-slate-800">Pedidos</h3>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table v-if="pedidos.length" class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3">Plan</th>
                                <th class="px-5 py-3">Estado</th>
                                <th class="px-5 py-3">Monto</th>
                                <th class="px-5 py-3">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="pedido in pedidos" :key="pedido.id" class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ pedido.plan.nombre }}</td>
                                <td class="px-5 py-4">
                                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', estadoClases[pedido.estado]]">
                                        {{ estadoLabels[pedido.estado] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ formatMoney(pedido.monto) }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ formatFecha(pedido.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="px-5 py-10 text-center text-sm text-slate-400">
                        Este cliente aún no tiene pedidos.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
