<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    planes: Array,
});

const form = useForm({
    nombre: '',
    telefono: '',
    plan_id: '',
    notas: '',
});

function formatMoney(cents) {
    return '$' + Math.round(cents / 100).toLocaleString('es-MX') + ' MXN';
}

function submit() {
    form.post(route('pedidos.store'));
}
</script>

<template>
    <Head title="Registrar pedido" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Registrar pedido</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <p class="mb-5 text-sm text-slate-500">
                Usa este formulario para dar de alta un pedido tomado por teléfono o en persona. Los pedidos que llegan por el bot de WhatsApp se registran automáticamente.
            </p>

            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <InputLabel for="nombre" value="Nombre del cliente" />
                    <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required autofocus />
                    <InputError class="mt-2" :message="form.errors.nombre" />
                </div>

                <div>
                    <InputLabel for="telefono" value="Teléfono (WhatsApp)" />
                    <TextInput
                        id="telefono"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.telefono"
                        placeholder="521XXXXXXXXXX"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.telefono" />
                </div>

                <div>
                    <InputLabel for="plan_id" value="Plan" />
                    <select
                        id="plan_id"
                        v-model="form.plan_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="" disabled>Selecciona un plan</option>
                        <option v-for="plan in planes" :key="plan.id" :value="plan.id">
                            {{ plan.nombre }} — {{ formatMoney(plan.precio) }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.plan_id" />
                </div>

                <div>
                    <InputLabel for="notas" value="Notas (opcional)" />
                    <textarea
                        id="notas"
                        v-model="form.notas"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Detalles adicionales del pedido…"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.notas" />
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('pedidos.index')">
                        <SecondaryButton type="button">Cancelar</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">Guardar pedido</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
