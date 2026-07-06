<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    telefono: '',
    email: '',
    notas: '',
});

function submit() {
    form.post(route('clientes.store'));
}
</script>

<template>
    <Head title="Nuevo cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Nuevo cliente</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <InputLabel for="nombre" value="Nombre" />
                    <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required autofocus />
                    <InputError class="mt-2" :message="form.errors.nombre" />
                </div>

                <div>
                    <InputLabel for="telefono" value="Teléfono" />
                    <TextInput id="telefono" type="text" class="mt-1 block w-full" v-model="form.telefono" required />
                    <InputError class="mt-2" :message="form.errors.telefono" />
                </div>

                <div>
                    <InputLabel for="email" value="Email (opcional)" />
                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="notas" value="Notas (opcional)" />
                    <textarea
                        id="notas"
                        v-model="form.notas"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.notas" />
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('clientes.index')">
                        <SecondaryButton type="button">Cancelar</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">Guardar cliente</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
