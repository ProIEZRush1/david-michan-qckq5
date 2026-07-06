<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDeleteButton from '@/Components/ConfirmDeleteButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    clientes: Array,
    buscar: String,
});

const search = ref(props.buscar ?? '');

function buscarClientes() {
    router.get(route('clientes.index'), { buscar: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Clientes</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm text-slate-500">
                    Los clientes que han conversado con el bot de WhatsApp y sus pedidos de líneas telefónicas.
                </p>
                <Link
                    :href="route('clientes.create')"
                    class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:from-violet-500 hover:to-fuchsia-500"
                >
                    + Nuevo cliente
                </Link>
            </div>

            <form @submit.prevent="buscarClientes" class="flex items-center gap-3">
                <input
                    type="text"
                    v-model="search"
                    placeholder="Buscar por nombre o teléfono..."
                    class="w-full max-w-sm rounded-xl border-slate-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500"
                />
                <button
                    type="submit"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50"
                >
                    Buscar
                </button>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">Nombre</th>
                            <th class="px-5 py-3">Teléfono</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Pedidos</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="cliente in clientes" :key="cliente.id" class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <Link
                                    :href="route('clientes.show', cliente.id)"
                                    class="font-semibold text-violet-600 hover:text-violet-800 hover:underline"
                                >
                                    {{ cliente.nombre }}
                                </Link>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ cliente.telefono }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ cliente.email || '—' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ cliente.pedidos_count }}</td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="route('clientes.edit', cliente.id)"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-violet-600 transition hover:bg-violet-50"
                                >
                                    Editar
                                </Link>
                                <ConfirmDeleteButton
                                    :href="route('clientes.destroy', cliente.id)"
                                    texto="Se eliminará el cliente permanentemente."
                                />
                            </td>
                        </tr>
                        <tr v-if="!clientes.length">
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                                Aún no tienes clientes registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
