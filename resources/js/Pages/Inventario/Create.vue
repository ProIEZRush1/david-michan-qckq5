<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    lada: '',
    numeros: '',
});

function submit() {
    form.post(route('inventario.store'));
}
</script>

<template>
    <Head title="Agregar números" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Agregar números al inventario</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <InputLabel for="lada" value="Lada (opcional)" />
                    <TextInput id="lada" type="text" class="mt-1 block w-full" v-model="form.lada" placeholder="Ej. 55" />
                    <InputError class="mt-2" :message="form.errors.lada" />
                </div>

                <div>
                    <InputLabel for="numeros" value="Números" />
                    <textarea
                        id="numeros"
                        v-model="form.numeros"
                        rows="8"
                        required
                        placeholder="Un número por línea, ej:&#10;5511122233&#10;5511122234&#10;5511122235"
                        class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                    <p class="mt-1 text-xs text-slate-400">
                        Captura un número por línea (también puedes separarlos por comas). Se agregarán al inventario como disponibles.
                    </p>
                    <InputError class="mt-2" :message="form.errors.numeros" />
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('inventario.index')">
                        <SecondaryButton type="button">Cancelar</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">Guardar números</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
