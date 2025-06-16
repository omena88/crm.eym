<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    UserIcon, 
    PhoneIcon, 
    EnvelopeIcon,
    BuildingOfficeIcon,
    PencilIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    contacto: Object,
});
</script>

<template>
    <Head :title="`Contacto - ${contacto.nombre_completo}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100 mb-4">
                        Detalle del Contacto
                    </h2>
                    <!-- Pestañas -->
                    <nav class="flex space-x-8">
                        <Link 
                            :href="route('clientes.index')"
                            class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            Clientes
                        </Link>
                        <Link 
                            :href="route('contactos.index')"
                            class="border-blue-500 text-blue-400 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            Contactos
                        </Link>
                    </nav>
                </div>
                <div class="flex items-center space-x-3">
                    <Link 
                        :href="route('contactos.edit', contacto.id)" 
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md border border-yellow-500 transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-500"
                    >
                        <PencilIcon class="w-4 h-4 mr-2" />
                        Editar
                    </Link>
                    <Link 
                        :href="route('contactos.index')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors"
                    >
                        Volver a Contactos
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Información Principal del Contacto -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <UserIcon class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-100">{{ contacto.nombre_completo }}</h1>
                                <p v-if="contacto.puesto" class="text-gray-400 text-lg">{{ contacto.puesto }}</p>
                                <div class="flex items-center mt-2">
                                    <span v-if="contacto.es_contacto_principal" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-300">
                                        <CheckIcon class="w-4 h-4 mr-1" />
                                        Contacto Principal
                                    </span>
                                    <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 border border-gray-300">
                                        <XMarkIcon class="w-4 h-4 mr-1" />
                                        Contacto Secundario
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-if="contacto.email" class="flex items-center">
                            <EnvelopeIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <div>
                                <p class="text-sm text-gray-400">Email</p>
                                <a :href="`mailto:${contacto.email}`" class="text-blue-400 hover:text-blue-300 transition-colors">
                                    {{ contacto.email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="contacto.celular" class="flex items-center">
                            <PhoneIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <div>
                                <p class="text-sm text-gray-400">Celular</p>
                                <a :href="`tel:${contacto.celular}`" class="text-blue-400 hover:text-blue-300 transition-colors">
                                    {{ contacto.celular }}
                                </a>
                            </div>
                        </div>

                        <div v-if="contacto.telefono && contacto.telefono !== contacto.celular" class="flex items-center">
                            <PhoneIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <div>
                                <p class="text-sm text-gray-400">Teléfono</p>
                                <a :href="`tel:${contacto.telefono}`" class="text-blue-400 hover:text-blue-300 transition-colors">
                                    {{ contacto.telefono }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Cliente -->
                <div v-if="contacto.cliente" class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-100 mb-4 flex items-center">
                        <BuildingOfficeIcon class="w-5 h-5 mr-2" />
                        Cliente Asociado
                    </h3>
                    
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                            <BuildingOfficeIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <Link 
                                :href="route('clientes.show', contacto.cliente.id)"
                                class="text-xl font-semibold text-blue-400 hover:text-blue-300 transition-colors"
                            >
                                {{ contacto.cliente.razon_social }}
                            </Link>
                            <p class="text-gray-400">{{ contacto.cliente.codigo }} • RUC: {{ contacto.cliente.ruc }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-if="contacto.cliente.sector">
                            <p class="text-sm text-gray-400">Sector</p>
                            <p class="text-gray-100">{{ contacto.cliente.sector }}</p>
                        </div>
                        
                        <div v-if="contacto.cliente.estado">
                            <p class="text-sm text-gray-400">Estado</p>
                            <p class="text-gray-100">{{ contacto.cliente.estado }}</p>
                        </div>

                        <div v-if="contacto.cliente.telefono">
                            <p class="text-sm text-gray-400">Teléfono Empresa</p>
                            <a :href="`tel:${contacto.cliente.telefono}`" class="text-blue-400 hover:text-blue-300 transition-colors">
                                {{ contacto.cliente.telefono }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Notas adicionales -->
                <div v-if="contacto.notas" class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-100 mb-4">Notas</h3>
                    <div class="bg-gray-800 rounded-lg p-4">
                        <p class="text-gray-300 whitespace-pre-wrap">{{ contacto.notas }}</p>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-100 mb-4">Información del Sistema</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-400">Fecha de Creación</p>
                            <p class="text-gray-100">{{ new Date(contacto.created_at).toLocaleDateString('es-PE', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Última Actualización</p>
                            <p class="text-gray-100">{{ new Date(contacto.updated_at).toLocaleDateString('es-PE', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 