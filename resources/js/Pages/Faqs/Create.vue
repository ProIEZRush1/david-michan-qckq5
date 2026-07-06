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
    pregunta: '',
    respuesta: '',
    palabras_clave: '',
    categoria: '',
    activo: true,
    orden: 0,
});

function submit() {
    form.post(route('preguntas-frecuentes.store'));
}
</script>

<template>
    <Head title="Nueva pregunta frecuente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Nueva pregunta frecuente</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <InputLabel for="pregunta" value="Pregunta" />
                    <TextInput id="pregunta" type="text" class="mt-1 block w-full" v-model="form.pregunta" required autofocus />
                    <InputError class="mt-2" :message="form.errors.pregunta" />
                </div>

                <div>
                    <InputLabel for="respuesta" value="Respuesta" />
                    <textarea
                        id="respuesta"
                        v-model="form.respuesta"
                        rows="4"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.respuesta" />
                </div>

                <div>
                    <InputLabel for="palabras_clave" value="Palabras clave" />
                    <TextInput id="palabras_clave" type="text" class="mt-1 block w-full" v-model="form.palabras_clave" required />
                    <p class="mt-1 text-xs text-slate-400">Palabras separadas por comas que el bot busca en el mensaje del cliente, ej: cobertura, señal, alcance</p>
                    <InputError class="mt-2" :message="form.errors.palabras_clave" />
                </div>

                <div>
                    <InputLabel for="categoria" value="Categoría (opcional)" />
                    <TextInput id="categoria" type="text" class="mt-1 block w-full" v-model="form.categoria" />
                    <InputError class="mt-2" :message="form.errors.categoria" />
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.activo" />
                        <span class="text-sm text-slate-600">Pregunta activa (el bot puede usarla para responder)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('preguntas-frecuentes.index')">
                        <SecondaryButton type="button">Cancelar</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">Guardar pregunta</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
