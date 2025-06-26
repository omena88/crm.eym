<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    BriefcaseIcon, 
    DocumentTextIcon, 
    CalendarDaysIcon,
    ShoppingCartIcon,
    UserCircleIcon,
    MapPinIcon,
    PhoneIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    cliente: Object,
    historial: Array,
});

const getIcon = (tipo) => {
    switch (tipo) {
        case 'Visita':
            return BriefcaseIcon;
        case 'Cotización':
            return DocumentTextIcon;
        case 'Pedido':
            return ShoppingCartIcon;
        default:
            return CalendarDaysIcon;
    }
};

const getColor = (tipo) => {
    switch (tipo) {
        case 'Visita':
            return 'bg-sky-500';
        case 'Cotización':
            return 'bg-amber-500';
        case 'Pedido':
            return 'bg-emerald-500';
        default:
            return 'bg-gray-500';
    }
};
</script>

<template>
    <Head :title="`Historial de ${cliente.razon_social}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('clientes.index')" class="text-slate-400 hover:text-slate-200">
                    &larr; Volver a Clientes
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Historial del Cliente
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Tarjeta de Información del Cliente -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ cliente.razon_social }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center space-x-2">
                            <UserCircleIcon class="h-5 w-5 text-gray-500" />
                            <span>{{ cliente.nombre_comercial }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <MapPinIcon class="h-5 w-5 text-gray-500" />
                            <span>{{ cliente.direccion }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <PhoneIcon class="h-5 w-5 text-gray-500" />
                            <span>{{ cliente.telefono }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <GlobeAltIcon class="h-5 w-5 text-gray-500" />
                            <a :href="cliente.website" target="_blank" class="text-indigo-400 hover:text-indigo-300">{{ cliente.website }}</a>
                        </div>
                    </div>
                </div>

                <!-- Línea de Tiempo del Historial -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Línea de Tiempo</h3>
                    <div class="relative border-l border-gray-200 dark:border-gray-700">                  
                        <div v-for="(item, index) in historial" :key="index" class="mb-10 ml-6">            
                            <span :class="['absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white dark:ring-gray-800', getColor(item.tipo)]">
                                <component :is="getIcon(item.tipo)" class="w-4 h-4 text-white"/>
                            </span>
                            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-700/50 dark:border-gray-600">
                                <div class="items-center justify-between mb-3 sm:flex">
                                    <time class="mb-1 text-xs font-normal text-gray-400 sm:order-last sm:mb-0">{{ new Date(item.fecha).toLocaleDateString() }}</time>
                                    <div class="text-sm font-normal text-gray-500 lex dark:text-gray-300">
                                        <h4 class="font-semibold text-base text-gray-900 dark:text-white">{{ item.titulo }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ item.descripcion }}
                                        </p>
                                    </div>
                                </div>
                                <div class="px-3 py-2 text-xs font-medium border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-600 dark:border-gray-500">
                                    <span class="font-semibold">{{ item.tipo }}</span> por {{ item.usuario }} - <span class="text-indigo-400">{{ item.estado }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 