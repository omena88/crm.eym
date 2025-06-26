<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, CheckCircleIcon, XCircleIcon, BanknotesIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    visita: Object,
    cotizacion: Object,
    userRole: String,
});

const form = useForm({
    visita_id: props.visita.id,
    nuevo_estado: '',
});

const cambiarEstado = (nuevoEstado) => {
    form.nuevo_estado = nuevoEstado;
    form.post(route('cotizaciones.simularAccion'), {
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
   return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(value);
};
</script>

<template>
    <Head title="Revisar Precios de Cotización" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('cotizaciones.index')" class="text-gray-400 hover:text-white">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-100">
                        Revisión de Precios (Gerencia)
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Cotización para: <span class="font-medium text-white">{{ visita.cliente.razon_social }}</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="userRole !== 'gerente'" class="bg-red-900/80 border border-red-700 text-red-300 px-4 py-3 rounded-lg mb-6 text-center">
                    <p class="font-bold">Acceso Denegado</p>
                    <p class="text-sm">Esta vista solo está disponible para el rol de Gerente.</p>
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-800 bg-gray-950/50">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <BanknotesIcon class="w-6 h-6 mr-3 text-green-400"/>
                            Detalle de la Cotización
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="producto in cotizacion.productos" :key="producto.id" class="p-4 bg-gray-800/50 rounded-lg border border-gray-700 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-white">{{ producto.nombre }}</p>
                                <p class="text-sm text-gray-400">{{ producto.descripcion }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-white">{{ formatCurrency(producto.precio_base * producto.cantidad) }}</p>
                                <p class="text-sm text-gray-400">{{ producto.cantidad }} x {{ formatCurrency(producto.precio_base) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-950/50 flex justify-between items-center">
                        <span class="text-xl font-bold text-white">TOTAL</span>
                        <span class="text-2xl font-bold text-green-400">{{ formatCurrency(cotizacion.total) }}</span>
                    </div>

                    <div v-if="userRole === 'gerente'" class="p-6 border-t border-gray-800">
                        <h3 class="text-lg font-semibold text-white mb-4 text-center">Decisión de Gerencia</h3>
                        <div class="flex justify-center space-x-4">
                            <button @click="cambiarEstado('PreciosAprobados')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <CheckCircleIcon class="w-5 h-5 mr-2" />
                                Aprobar Precios
                            </button>
                            <button @click="cambiarEstado('PreciosRechazados')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <XCircleIcon class="w-5 h-5 mr-2" />
                                Rechazar Precios
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-4 text-center">Si se rechaza, se solicitará al vendedor que ajuste la cotización y la reenvíe.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 