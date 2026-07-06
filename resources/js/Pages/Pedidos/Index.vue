<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pedidos: Array,
    estado: { type: String, default: null },
});

const filtros = [
    { value: null, label: 'Todos' },
    { value: 'iniciado', label: 'Iniciado' },
    { value: 'en_pago', label: 'En pago' },
    { value: 'pagado', label: 'Pagado' },
    { value: 'numero_asignado', label: 'Número asignado' },
    { value: 'entregado', label: 'Entregado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const badgeClasses = {
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
    if (cents === null || cents === undefined) return '—';
    return '$' + Math.round(cents / 100).toLocaleString('es-MX') + ' MXN';
}

function formatFecha(fecha) {
    if (!fecha) return '—';
    return new Date(fecha).toLocaleDateString('es-MX');
}
</script>

<template>
    <Head title="Pedidos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Pedidos</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Los pedidos que el bot de WhatsApp cierra con tus clientes, desde el pago hasta la entrega de la línea.
                </p>
                <Link
                    :href="route('pedidos.create')"
                    class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:from-violet-500 hover:to-fuchsia-500"
                >
                    + Registrar pedido
                </Link>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="filtro in filtros"
                    :key="filtro.label"
                    :href="route('pedidos.index', filtro.value ? { estado: filtro.value } : {})"
                    :class="[
                        'rounded-full px-3.5 py-1.5 text-xs font-semibold transition',
                        estado === filtro.value
                            ? 'bg-gradient-to-r from-[#7c3aed] to-[#c026d3] text-white shadow-md shadow-fuchsia-500/20'
                            : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50',
                    ]"
                >
                    {{ filtro.label }}
                </Link>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">Cliente</th>
                            <th class="px-5 py-3">Plan</th>
                            <th class="px-5 py-3">Monto</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3">Número</th>
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="pedido in pedidos" :key="pedido.id" class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ pedido.cliente?.nombre ?? pedido.telefono }}</p>
                                <p v-if="pedido.cliente?.nombre" class="text-xs text-slate-400">{{ pedido.telefono }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ pedido.plan?.nombre ?? '—' }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ formatMoney(pedido.monto) }}</td>
                            <td class="px-5 py-4">
                                <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', badgeClasses[pedido.estado]]">
                                    {{ estadoLabels[pedido.estado] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ pedido.numero_telefonico?.numero ?? '—' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ formatFecha(pedido.created_at) }}</td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="route('pedidos.show', pedido.id)"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-violet-600 transition hover:bg-violet-50"
                                >
                                    Ver
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!pedidos.length">
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                                Aún no hay pedidos registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
