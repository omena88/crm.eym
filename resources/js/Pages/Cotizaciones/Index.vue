<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { EyeIcon, DocumentPlusIcon } from '@heroicons/vue/24/outline';

defineProps({
    visitas: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Cotizaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-100">
                Módulo de Cotizaciones
            </h2>
            <p class="text-sm text-gray-400 mt-1">
                Selecciona una visita para crear o ver su cotización asociada.
            </p>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-800">
                            <thead class="bg-gray-950">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Visita</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Fecha Programada</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-900 divide-y divide-gray-800">
                                <tr v-for="visita in visitas" :key="visita.id" class="hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-100">{{ visita.titulo }}</div>
                                        <div v-if="visita.descripcion" class="text-sm text-gray-400 truncate max-w-xs">{{ visita.descripcion }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                                        {{ visita.cliente.razon_social }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                                        {{ formatDate(visita.fecha_programada) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('cotizaciones.show', { visita: visita.id })" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                                            <DocumentPlusIcon class="w-4 h-4 mr-2" />
                                            Gestionar Cotización
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="visitas.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-400">
                                        No hay visitas recientes para mostrar.
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