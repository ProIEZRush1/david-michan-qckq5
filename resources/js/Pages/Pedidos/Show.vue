<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    pedido: Object,
    numerosDisponibles: Number,
});

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
    return new Date(fecha).toLocaleString('es-MX');
}

function marcarPagado() {
    Swal.fire({
        title: 'Marcar como pagado',
        text: `Se confirmará el pago y se intentará asignar automáticamente un número del inventario. Hay ${props.numerosDisponibles} números disponibles en inventario.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar como pagado',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#7c3aed',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('pedidos.marcar-pagado', props.pedido.id));
        }
    });
}

function marcarEntregado() {
    Swal.fire({
        title: 'Marcar como entregado',
        text: 'Se notificará al cliente que su línea telefónica ha sido entregada.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar como entregado',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('pedidos.marcar-entregado', props.pedido.id));
        }
    });
}

function cancelarPedido() {
    Swal.fire({
        title: 'Cancelar pedido',
        text: 'Se cancelará el pedido y, si ya tenía un número asignado, se liberará de vuelta al inventario.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar pedido',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#dc2626',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('pedidos.cancelar', props.pedido.id));
        }
    });
}
</script>

<template>
    <Head :title="`Pedido #${pedido.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Pedido #{{ pedido.id }}</h2>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <Link :href="route('pedidos.index')" class="inline-flex items-center gap-1 text-sm font-semibold text-violet-600 hover:text-violet-500">
                ← Volver a pedidos
            </Link>

            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cliente</p>
                        <p class="mt-1 text-lg font-bold text-slate-800">{{ pedido.cliente?.nombre ?? pedido.telefono }}</p>
                        <p class="text-sm text-slate-500">{{ pedido.telefono }}</p>
                        <p v-if="pedido.cliente?.email" class="text-sm text-slate-500">{{ pedido.cliente.email }}</p>
                    </div>
                    <span :class="['rounded-full px-4 py-2 text-sm font-semibold', badgeClasses[pedido.estado]]">
                        {{ estadoLabels[pedido.estado] }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-6 py-6 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Plan</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ pedido.plan?.nombre ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Monto</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ formatMoney(pedido.monto) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pedido creado</p>
                        <p class="mt-1 text-slate-600">{{ formatFecha(pedido.created_at) }}</p>
                    </div>
                    <div v-if="pedido.pagado_at">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pagado el</p>
                        <p class="mt-1 text-slate-600">{{ formatFecha(pedido.pagado_at) }}</p>
                    </div>
                    <div v-if="pedido.numero_asignado_at">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Número asignado el</p>
                        <p class="mt-1 text-slate-600">{{ formatFecha(pedido.numero_asignado_at) }}</p>
                    </div>
                    <div v-if="pedido.entregado_at">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Entregado el</p>
                        <p class="mt-1 text-slate-600">{{ formatFecha(pedido.entregado_at) }}</p>
                    </div>
                </div>

                <div v-if="pedido.numero_telefonico" class="mb-6 rounded-xl bg-violet-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-500">Línea telefónica asignada</p>
                    <p class="mt-1 text-2xl font-bold text-violet-800">
                        {{ pedido.numero_telefonico.lada ? `(${pedido.numero_telefonico.lada}) ` : '' }}{{ pedido.numero_telefonico.numero }}
                    </p>
                </div>

                <div v-if="pedido.notas" class="mb-2 rounded-xl bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Notas</p>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ pedido.notas }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-slate-400">Acciones</p>
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        v-if="pedido.estado === 'iniciado' || pedido.estado === 'en_pago'"
                        type="button"
                        @click="marcarPagado"
                        class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:from-violet-500 hover:to-fuchsia-500"
                    >
                        Marcar como pagado
                    </button>

                    <button
                        v-if="pedido.estado === 'numero_asignado'"
                        type="button"
                        @click="marcarEntregado"
                        class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 transition hover:bg-emerald-500"
                    >
                        Marcar como entregado
                    </button>

                    <DangerButton
                        v-if="pedido.estado !== 'entregado' && pedido.estado !== 'cancelado'"
                        type="button"
                        @click="cancelarPedido"
                        class="!rounded-xl !normal-case !tracking-normal"
                    >
                        Cancelar pedido
                    </DangerButton>

                    <div class="ml-auto">
                        <ConfirmDeleteButton
                            :href="route('pedidos.destroy', pedido.id)"
                            label="Eliminar pedido"
                            texto="Se eliminará el pedido permanentemente. Si tenía un número asignado, se liberará de vuelta al inventario."
                        />
                    </div>
                </div>

                <p v-if="pedido.estado === 'pagado'" class="mt-4 text-sm text-amber-600">
                    Este pedido está pagado pero aún no tiene un número asignado — probablemente el inventario está vacío. Agrega números disponibles y vuelve a intentar marcarlo como pagado.
                </p>
                <p v-if="pedido.estado === 'entregado'" class="mt-4 text-sm text-emerald-600">
                    Este pedido ya fue entregado al cliente. No quedan más acciones de flujo por hacer.
                </p>
                <p v-if="pedido.estado === 'cancelado'" class="mt-4 text-sm text-slate-400">
                    Este pedido fue cancelado.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
