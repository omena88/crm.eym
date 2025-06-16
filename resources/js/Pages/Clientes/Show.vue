<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    BuildingOfficeIcon, 
    UserIcon, 
    PhoneIcon, 
    EnvelopeIcon, 
    GlobeAltIcon, 
    MapPinIcon,
    PencilIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    cliente: Object,
    contactos: Array,
    visitas: Object,
});

// Función para obtener color del estado
const getEstadoBadgeColor = (estado) => {
    const colors = {
        'Pendiente': 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'Visitado': 'bg-blue-100 text-blue-800 border-blue-300',
        'Por cotizar': 'bg-purple-100 text-purple-800 border-purple-300',
        'Cotizado': 'bg-orange-100 text-orange-800 border-orange-300',
        'Aprobado': 'bg-green-100 text-green-800 border-green-300',
        'Rechazado': 'bg-red-100 text-red-800 border-red-300',
    };
    return colors[estado] || 'bg-gray-100 text-gray-800 border-gray-300';
};
</script>

<template>
    <Head :title="`Cliente - ${cliente.razon_social}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100 mb-4">
                        Detalles del Cliente
                    </h2>
                    <!-- Pestañas -->
                    <nav class="flex space-x-8">
                        <Link 
                            :href="route('clientes.index')"
                            class="border-blue-500 text-blue-400 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            Clientes
                        </Link>
                        <Link 
                            :href="route('contactos.index')"
                            class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            Contactos
                        </Link>
                    </nav>
                </div>
                <div class="flex items-center space-x-3">
                    <Link 
                        :href="route('clientes.edit', cliente.id)"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition-colors"
                    >
                        <PencilIcon class="w-4 h-4 mr-2" />
                        Editar
                    </Link>
                    <Link 
                        :href="route('clientes.index')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors"
                    >
                        Volver a Clientes
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Información Principal -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <BuildingOfficeIcon class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-100">{{ cliente.razon_social }}</h1>
                                <p class="text-gray-400">{{ cliente.codigo }} • RUC: {{ cliente.ruc }}</p>
                            </div>
                        </div>
                        <span :class="getEstadoBadgeColor(cliente.estado)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border">
                            {{ cliente.estado }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-if="cliente.telefono" class="flex items-center">
                            <PhoneIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <div>
                                <p class="text-sm text-gray-400">Teléfono</p>
                                <p class="text-gray-100">{{ cliente.telefono }}</p>
                            </div>
                        </div>

                        <div v-if="cliente.website" class="flex items-center">
                            <GlobeAltIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <div>
                                <p class="text-sm text-gray-400">Sitio Web</p>
                                <a :href="cliente.website" target="_blank" class="text-blue-400 hover:text-blue-300 transition-colors">
                                    {{ cliente.website }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <BuildingOfficeIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <div>
                                <p class="text-sm text-gray-400">Sector</p>
                                <p class="text-gray-100">{{ cliente.sector }}</p>
                            </div>
                        </div>

                        <div v-if="cliente.direccion" class="flex items-center">
                            <MapPinIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <div>
                                <p class="text-sm text-gray-400">Dirección</p>
                                <p class="text-gray-100">{{ cliente.direccion }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="cliente.notas" class="mt-6 pt-6 border-t border-gray-800">
                        <h3 class="text-sm font-medium text-gray-300 mb-2">Notas</h3>
                        <p class="text-gray-100">{{ cliente.notas }}</p>
                    </div>
                </div>

                <!-- Contactos -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-100 mb-4 flex items-center">
                        <UserIcon class="w-5 h-5 mr-2" />
                        Contactos
                    </h3>

                    <div v-if="contactos.length > 0" class="space-y-4">
                        <div 
                            v-for="contacto in contactos" 
                            :key="contacto.id"
                            class="bg-gray-800 rounded-lg p-4"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center">
                                        <h4 class="text-gray-100 font-medium">{{ contacto.nombre_completo }}</h4>
                                        <span v-if="contacto.es_contacto_principal" class="ml-2 px-2 py-1 bg-green-600 text-white text-xs rounded-full">
                                            Principal
                                        </span>
                                    </div>
                                    <p v-if="contacto.puesto" class="text-gray-400 text-sm">{{ contacto.puesto }}</p>
                                    
                                    <div class="mt-2 space-y-1">
                                        <div v-if="contacto.email" class="flex items-center text-sm text-gray-300">
                                            <EnvelopeIcon class="w-4 h-4 mr-2" />
                                            {{ contacto.email }}
                                        </div>
                                        <div v-if="contacto.celular" class="flex items-center text-sm text-gray-300">
                                            <PhoneIcon class="w-4 h-4 mr-2" />
                                            {{ contacto.celular }}
                                        </div>
                                    </div>
                                </div>
                                <Link 
                                    :href="route('contactos.edit', contacto.id)"
                                    class="text-blue-400 hover:text-blue-300 transition-colors"
                                    title="Editar contacto"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <UserIcon class="mx-auto h-12 w-12 text-gray-500" />
                        <h3 class="mt-2 text-sm font-medium text-gray-100">Sin contactos</h3>
                        <p class="mt-1 text-sm text-gray-400">Este cliente no tiene contactos registrados.</p>
                    </div>
                </div>

                <!-- Visitas Recientes -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-100 mb-4">Visitas Recientes</h3>

                    <div v-if="visitas.data.length > 0" class="space-y-4">
                        <div 
                            v-for="visita in visitas.data" 
                            :key="visita.id"
                            class="bg-gray-800 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-gray-100 font-medium">{{ visita.titulo }}</h4>
                                    <p class="text-gray-400 text-sm">{{ visita.fecha_programada }}</p>
                                </div>
                                <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded-full">
                                    {{ visita.estado }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <h3 class="mt-2 text-sm font-medium text-gray-100">Sin visitas</h3>
                        <p class="mt-1 text-sm text-gray-400">Este cliente no tiene visitas registradas.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 