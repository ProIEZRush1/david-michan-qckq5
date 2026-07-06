<script setup>
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    href: { type: String, required: true },
    label: { type: String, default: 'Eliminar' },
    texto: { type: String, default: 'Esta acción no se puede deshacer.' },
});

function confirmar() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: props.texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(props.href, { preserveScroll: true });
        }
    });
}
</script>

<template>
    <button
        type="button"
        @click="confirmar"
        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
    >
        {{ label }}
    </button>
</template>
