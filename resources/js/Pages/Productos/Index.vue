<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    ChevronDownIcon,
    DocumentArrowDownIcon,
    TagIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    productos: Object,
});

const openAccordion = ref(null);

const toggleAccordion = (id) => {
    openAccordion.value = openAccordion.value === id ? null : id;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(value);
};
</script>

<template>
    <Head title="Productos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Mantenimiento de Productos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Listado de Productos</h3>
                            <Link :href="route('dashboard')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Nuevo Producto
                            </Link>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="producto in productos.data" :key="producto.id" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                                <button @click="toggleAccordion(producto.id)" class="w-full flex justify-between items-center p-4 text-left">
                                    <div class="flex items-center space-x-4">
                                        <TagIcon class="w-6 h-6 text-gray-500" />
                                        <div>
                                            <p class="font-semibold">{{ producto.nombre }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ producto.codigo }} - {{ producto.unidad }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <span class="font-semibold text-lg">{{ formatCurrency(producto.precio_base) }}</span>
                                        <ChevronDownIcon class="w-6 h-6 transition-transform" :class="{ 'rotate-180': openAccordion === producto.id }" />
                                    </div>
                                </button>
                                
                                <div v-if="openAccordion === producto.id" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{{ producto.descripcion }}</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-semibold mb-2 flex items-center"><CurrencyDollarIcon class="w-5 h-5 mr-2" /> Precios por Canal</h4>
                                            <ul class="space-y-2">
                                                <li v-for="canal in producto.canales" :key="canal.id" class="flex justify-between p-2 rounded-md bg-gray-100 dark:bg-gray-800">
                                                    <span class="text-sm">{{ canal.nombre }}</span>
                                                    <span class="font-medium text-sm">{{ formatCurrency(canal.pivot.precio) }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold mb-2 flex items-center"><DocumentArrowDownIcon class="w-5 h-5 mr-2" /> Documentos Adjuntos</h4>
                                            <ul class="space-y-2">
                                                 <li v-for="doc in producto.documentos" :key="doc.id" class="p-2 rounded-md bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
                                                    <a :href="`/storage/${doc.ruta}`" target="_blank" class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                                        <DocumentArrowDownIcon class="w-4 h-4" />
                                                        <span>{{ doc.nombre }} ({{ doc.tipo_archivo }})</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
