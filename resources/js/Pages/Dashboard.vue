<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    modulos: Object,
});

const page = usePage();

const businessName = computed(() => page.props.name ?? 'Mi Negocio');
const userFirstName = computed(() => {
    const name = (page.props.auth?.user?.name ?? '').trim();
    return name ? name.split(/\s+/)[0] : '';
});

function formatMoney(cents) {
    return '$' + Math.round((cents || 0) / 100).toLocaleString('es-MX') + ' MXN';
}

const stats = computed(() => [
    {
        label: 'Clientes activos',
        value: String(props.stats.clientes),
        hint: 'Tus contactos registrados',
        gradient: 'from-[#7c3aed] to-[#a855f7]',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
    {
        label: 'Pedidos del mes',
        value: String(props.stats.pedidosMes),
        hint: 'Actividad reciente',
        gradient: 'from-[#a21caf] to-[#c026d3]',
        icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293A1 1 0 005.414 17H17M17 17a2 2 0 100 4 2 2 0 000-4zM9 19a2 2 0 11-4 0 2 2 0 014 0z',
    },
    {
        label: 'Ingresos',
        value: formatMoney(props.stats.ingresos),
        hint: 'Pedidos pagados en adelante',
        gradient: 'from-[#7c3aed] to-[#c026d3]',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label: 'Pedidos pendientes',
        value: String(props.stats.pendientes),
        hint: 'Por cobrar / asignar número',
        gradient: 'from-[#c026d3] to-[#db2777]',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    },
]);

const modulos = computed(() => [
    { label: 'Planes', emoji: '📶', value: props.modulos.planes, href: route('planes.index') },
    { label: 'Inventario de números', emoji: '📱', value: props.modulos.numerosDisponibles, hint: 'disponibles', href: route('inventario.index') },
    { label: 'Pedidos', emoji: '🧾', value: props.modulos.pedidos, href: route('pedidos.index') },
    { label: 'Clientes', emoji: '👥', value: props.modulos.clientes, href: route('clientes.index') },
    { label: 'Preguntas frecuentes', emoji: '💬', value: props.modulos.faqs, href: route('preguntas-frecuentes.index') },
    { label: 'Conectar WhatsApp', emoji: '🔗', value: null, href: route('conectar') },
]);
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">
                Panel de control
            </h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Hero -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#7c3aed] to-[#c026d3] p-8 text-white shadow-xl shadow-fuchsia-500/20 sm:p-10"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"
                ></div>
                <div
                    class="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-fuchsia-300/20 blur-2xl"
                ></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                        Bienvenido a tu sistema
                    </p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">
                        Hola<span v-if="userFirstName">, {{ userFirstName }}</span> 👋
                    </h1>
                    <p class="mt-3 max-w-2xl text-base text-white/85">
                        Este es el panel de venta de líneas telefónicas de
                        <span class="font-semibold">{{ businessName }}</span>.
                        Desde aquí gestionas tus planes, tu inventario de números,
                        tus pedidos y a tus clientes — todo conectado con tu bot de WhatsApp.
                    </p>
                </div>
            </section>

            <!-- Stat cards -->
            <section>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                :class="[
                                    'flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md',
                                    stat.gradient,
                                ]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        :d="stat.icon"
                                    />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-extrabold text-slate-800">
                            {{ stat.value }}
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-600">
                            {{ stat.label }}
                        </p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ stat.hint }}</p>
                    </div>
                </div>
            </section>

            <!-- Módulos -->
            <section>
                <h3 class="mb-4 text-lg font-bold text-slate-800">Tus módulos</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="modulo in modulos"
                        :key="modulo.label"
                        :href="modulo.href"
                        class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-2xl">
                                {{ modulo.emoji }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ modulo.label }}</p>
                                <p v-if="modulo.value !== null" class="text-xs text-slate-400">
                                    {{ modulo.value }} {{ modulo.hint ?? 'registrados' }}
                                </p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-3 py-1 text-xs font-semibold text-white opacity-0 shadow-sm transition group-hover:opacity-100"
                        >
                            Ver / Administrar
                        </span>
                    </Link>
                </div>
            </section>

            <!-- Ayuda -->
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm lg:col-span-2"
                >
                    <h3 class="text-lg font-bold text-slate-800">
                        Tu bot de WhatsApp está listo
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Cuando un cliente escribe a tu número de
                        <span class="font-semibold text-slate-800">{{ businessName }}</span>,
                        el bot le muestra tus planes, responde sus preguntas frecuentes,
                        captura sus datos y registra su pedido automáticamente. Tú das
                        seguimiento a la venta desde el módulo de Pedidos.
                    </p>
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-700">
                                Conecta tu WhatsApp
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Escanea el código QR desde "Conectar WhatsApp".
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-700">
                                Da seguimiento a tus pedidos
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Confirma pagos y el sistema asigna el número automáticamente.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 p-8 text-white shadow-sm"
                >
                    <div>
                        <h3 class="text-lg font-bold">¿Necesitas ayuda?</h3>
                        <p class="mt-2 text-sm text-slate-300">
                            Estamos para acompañarte. Cualquier ajuste o nueva función
                            que necesites, lo resolvemos por ti.
                        </p>
                    </div>
                    <p class="mt-6 text-xs text-slate-400">
                        Plataforma impulsada por
                        <span class="font-semibold text-slate-200">Overcloud</span>
                    </p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
