<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    precio: '',
    descripcion: '',
    datos_gb: '',
    minutos_ilimitados: true,
    sms_ilimitados: true,
    vigencia_dias: 30,
    activo: true,
    orden: 0,
});

function submit() {
    form.post(route('planes.store'));
}
</script>

<template>
    <Head title="Nuevo plan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Nuevo plan</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <InputLabel for="nombre" value="Nombre del plan" />
                    <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required autofocus />
                    <InputError class="mt-2" :message="form.errors.nombre" />
                </div>

                <div>
                    <InputLabel for="precio" value="Precio mensual (MXN)" />
                    <TextInput id="precio" type="number" step="0.01" min="0" class="mt-1 block w-full" v-model="form.precio" required />
                    <InputError class="mt-2" :message="form.errors.precio" />
                </div>

                <div>
                    <InputLabel for="descripcion" value="Descripción" />
                    <textarea
                        id="descripcion"
                        v-model="form.descripcion"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.descripcion" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="datos_gb" value="Datos en GB (vacío = ilimitados)" />
                        <TextInput id="datos_gb" type="number" min="0" class="mt-1 block w-full" v-model="form.datos_gb" />
                        <InputError class="mt-2" :message="form.errors.datos_gb" />
                    </div>
                    <div>
                        <InputLabel for="vigencia_dias" value="Vigencia (días)" />
                        <TextInput id="vigencia_dias" type="number" min="1" class="mt-1 block w-full" v-model="form.vigencia_dias" required />
                        <InputError class="mt-2" :message="form.errors.vigencia_dias" />
                    </div>
                </div>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.minutos_ilimitados" />
                        <span class="text-sm text-slate-600">Minutos ilimitados</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.sms_ilimitados" />
                        <span class="text-sm text-slate-600">SMS ilimitados</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.activo" />
                        <span class="text-sm text-slate-600">Plan activo (visible en el bot)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('planes.index')">
                        <SecondaryButton type="button">Cancelar</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">Guardar plan</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
