<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3200,
    timerProgressBar: true,
});

watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            toast.fire({ icon: 'success', title: message });
        }
    },
    { immediate: true },
);

watch(
    () => page.props.errors,
    (errors) => {
        const messages = Object.values(errors ?? {});
        if (messages.length) {
            toast.fire({ icon: 'error', title: messages[0], timer: 4500 });
        }
    },
    { immediate: true },
);
</script>

<template></template>
