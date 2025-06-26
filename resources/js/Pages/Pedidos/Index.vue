<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PlusIcon, ShoppingCartIcon, ClockIcon, CheckCircleIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline';

defineProps({
    pedidos: Array,
    stats: Object,
    userRole: String,
});

const getEstadoColor = (estado) => {
    switch (estado) {
        case 'borrador': return 'bg-gray-100 text-gray-800';
        case 'revision': return 'bg-yellow-100 text-yellow-800';
        case 'aprobado': return 'bg-green-100 text-green-800';
        case 'enviado': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getEstadoIcon = (estado) => {
    switch (estado) {
        case 'borrador': return ClockIcon;
        case 'revision': return ClockIcon;
        case 'aprobado': return CheckCircleIcon;
        case 'enviado': return PaperAirplaneIcon;
        default: return ClockIcon;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP'
    }).format(value);
};
</script>

<template>
    <Head title="Pedidos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100">Gestión de Pedidos</h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Administra los pedidos procesados y su flujo de aprobación
                    </p>
                </div>
                <Link 
                    :href="route('pedidos.create')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Nuevo Pedido
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-600 rounded-lg">
                                <ShoppingCartIcon class="w-6 h-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Total</p>
                                <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-gray-600 rounded-lg">
                                <ClockIcon class="w-6 h-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Borrador</p>
                                <p class="text-2xl font-bold text-white">{{ stats.borrador }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-600 rounded-lg">
                                <ClockIcon class="w-6 h-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">En Revisión</p>
                                <p class="text-2xl font-bold text-white">{{ stats.revision }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-600 rounded-lg">
                                <CheckCircleIcon class="w-6 h-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Aprobados</p>
                                <p class="text-2xl font-bold text-white">{{ stats.aprobado }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-600 rounded-lg">
                                <PaperAirplaneIcon class="w-6 h-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Enviados</p>
                                <p class="text-2xl font-bold text-white">{{ stats.enviado }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Valor Total -->
                <div class="bg-gradient-to-r from-blue-900 to-blue-800 border border-blue-700 rounded-lg p-6 mb-8">
                    <div class="text-center">
                        <p class="text-sm font-medium text-blue-200">Valor Total de Pedidos</p>
                        <p class="text-3xl font-bold text-white">{{ formatCurrency(stats.total_valor) }}</p>
                    </div>
                </div>

                <!-- Lista de Pedidos -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-800">
                        <h3 class="text-lg font-semibold text-white">Lista de Pedidos</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-800">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Pedido
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Vendedor
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-900 divide-y divide-gray-800">
                                <tr v-for="pedido in pedidos" :key="pedido.id" class="hover:bg-gray-800 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ pedido.numero }}</div>
                                        <div class="text-sm text-gray-400">{{ pedido.productos_count }} productos</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-white">{{ pedido.cliente.razon_social }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-white">{{ pedido.vendedor.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ formatCurrency(pedido.total) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getEstadoColor(pedido.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            <component :is="getEstadoIcon(pedido.estado)" class="w-4 h-4 mr-1" />
                                            {{ pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ pedido.fecha_creacion }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link 
                                            :href="route('pedidos.show', pedido.id)" 
                                            class="text-blue-400 hover:text-blue-300 mr-3"
                                        >
                                            Ver
                                        </Link>
                                        <Link 
                                            v-if="pedido.estado === 'borrador'" 
                                            :href="route('pedidos.edit', pedido.id)" 
                                            class="text-green-400 hover:text-green-300"
                                        >
                                            Editar
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 