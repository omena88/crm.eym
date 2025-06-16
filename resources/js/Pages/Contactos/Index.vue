<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    ArrowDownTrayIcon,
    ArrowUpTrayIcon,
    PencilIcon,
    TrashIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    BuildingOfficeIcon
} from '@heroicons/vue/24/outline';
import {
    XMarkIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    contactos: {
        type: Object,
        default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 })
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, principales: 0, con_email: 0, con_telefono: 0 })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    sort: {
        type: Object,
        default: () => ({})
    },
    clientes: {
        type: Array,
        default: () => []
    },
});

// Filtros
const filters = ref({
    search: props.filters.search || '',
    cliente_id: props.filters.cliente_id || '',
    es_principal: props.filters.es_principal || '',
});

const showFilters = ref(false);

// Observar cambios en filtros con debounce para búsqueda en tiempo real
let searchTimeout;
watch(filters, (value) => {
    // Para la búsqueda, usar debounce para evitar muchas requests
    if (searchTimeout) clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        router.get(route('contactos.index'), value, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }, 300); // 300ms de debounce
}, { deep: true });

// Función para limpiar filtros
const clearFilters = () => {
    filters.value = {
        search: '',
        cliente_id: '',
        es_principal: '',
    };
};

// Función para exportar
const exportContactos = () => {
    window.location.href = route('contactos.export', filters.value);
};

// Función para eliminar contacto
const deleteContacto = (contacto) => {
    if (confirm(`¿Estás seguro de que deseas eliminar el contacto "${contacto.nombre_completo}"?`)) {
        router.delete(route('contactos.destroy', contacto.id));
    }
};
</script>

<template>
    <Head title="Contactos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100 mb-4">
                        Gestión de Clientes y Contactos
                    </h2>
                    <!-- Pestañas -->
                    <nav class="flex space-x-8">
                        <Link 
                            :href="route('clientes.index')"
                            :class="$page.component === 'Clientes/Index' 
                                ? 'border-blue-500 text-blue-400' 
                                : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            Clientes
                        </Link>
                        <Link 
                            :href="route('contactos.index')"
                            :class="$page.component === 'Contactos/Index' 
                                ? 'border-blue-500 text-blue-400' 
                                : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            Contactos
                        </Link>
                    </nav>
                </div>
                <Link 
                    :href="route('contactos.create')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md border border-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Nuevo Contacto
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Indicador de carga -->
                <div v-if="!contactos || !contactos.data" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-400">Cargando contactos...</span>
                </div>
                
                <template v-else>

                <!-- Filtros y búsqueda -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between mb-4">
                        <!-- Búsqueda -->
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Buscar por nombre, email o teléfono..."
                                    class="block w-full pl-10 pr-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                />
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex items-center space-x-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-100 text-sm font-medium rounded-md border border-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                            >
                                <FunnelIcon class="w-4 h-4 mr-2" />
                                Filtros
                                <span v-if="filters.cliente_id || filters.es_principal" class="ml-2 px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full">
                                    {{ (filters.cliente_id ? 1 : 0) + (filters.es_principal ? 1 : 0) }}
                                </span>
                            </button>

                            <button
                                @click="exportContactos"
                                class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md border border-green-500 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                                <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                                Exportar
                            </button>
                        </div>
                    </div>

                    <!-- Filtros expandibles -->
                    <div v-if="showFilters" class="border-t border-gray-800 pt-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Cliente</label>
                                <select
                                    v-model="filters.cliente_id"
                                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                >
                                    <option value="">Todos los clientes</option>
                                    <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                        {{ cliente.razon_social }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Tipo</label>
                                <select
                                    v-model="filters.es_principal" 
                                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                >
                                    <option value="">Todos los contactos</option>
                                    <option value="1">Solo principales</option>
                                    <option value="0">Solo secundarios</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button
                                    @click="clearFilters"
                                    class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-100 text-sm font-medium rounded-md border border-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                                >
                                    <XMarkIcon class="w-4 h-4 mr-2" />
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de contactos -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-800">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Contacto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Información de Contacto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-900 divide-y divide-gray-800">
                                <tr v-for="contacto in (contactos?.data || [])" :key="contacto.id" class="hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-100">
                                                {{ contacto.nombre_completo }}
                                            </div>
                                            <div v-if="contacto.puesto" class="text-sm text-gray-400">
                                                {{ contacto.puesto }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="contacto.cliente && contacto.cliente.id">
                                            <Link 
                                                :href="route('clientes.show', contacto.cliente.id)"
                                                class="text-sm font-medium text-blue-400 hover:text-blue-300 transition-colors"
                                            >
                                                {{ contacto.cliente.razon_social }}
                                            </Link>
                                            <div class="text-sm text-gray-400">
                                                {{ contacto.cliente.codigo }}
                                            </div>
                                        </div>
                                        <div v-else class="text-sm text-gray-500">
                                            Sin cliente
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div v-if="contacto.email" class="flex items-center text-sm text-gray-300">
                                                <EnvelopeIcon class="w-4 h-4 mr-2 flex-shrink-0" />
                                                {{ contacto.email }}
                                            </div>
                                            <div v-if="contacto.celular" class="flex items-center text-sm text-gray-300">
                                                <PhoneIcon class="w-4 h-4 mr-2 flex-shrink-0" />
                                                {{ contacto.celular }}
                                            </div>
                                            <div v-if="contacto.telefono && contacto.telefono !== contacto.celular" class="flex items-center text-sm text-gray-300">
                                                <PhoneIcon class="w-4 h-4 mr-2 flex-shrink-0" />
                                                {{ contacto.telefono }}
                                            </div>
                                            <div v-if="!contacto.email && !contacto.celular && !contacto.telefono" class="text-sm text-gray-500">
                                                Sin información de contacto
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="contacto.es_contacto_principal" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                            Principal
                                        </span>
                                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-300">
                                            Secundario
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link
                                                v-if="contacto.id"
                                                :href="route('contactos.edit', contacto.id)"
                                                class="text-yellow-400 hover:text-yellow-300 transition-colors p-1"
                                                title="Editar"
                                            >
                                                <PencilIcon class="w-4 h-4" />
                                            </Link>
                                            <button
                                                v-if="contacto.id"
                                                @click="deleteContacto(contacto)"
                                                class="text-red-400 hover:text-red-300 transition-colors p-1"
                                                title="Eliminar"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="contactos?.links && contactos.links.length > 3" class="bg-gray-800 px-4 py-3 border-t border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-400">
                                Mostrando {{ contactos?.from || 0 }} - {{ contactos?.to || 0 }} de {{ contactos?.total || 0 }} contactos
                            </div>
                            <nav class="flex items-center space-x-2">
                                <Link
                                    v-for="link in contactos.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                        link.active
                                            ? 'bg-blue-600 text-white border border-blue-500'
                                            : 'bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600',
                                        !link.url ? 'pointer-events-none opacity-50' : ''
                                    ]"
                                />
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay contactos -->
                <div v-if="(contactos?.data || []).length === 0" class="text-center py-12">
                    <UserIcon class="mx-auto h-12 w-12 text-gray-500" />
                    <h3 class="mt-2 text-lg font-medium text-gray-100">No hay contactos</h3>
                    <p class="mt-1 text-gray-400">
                        {{ filters.search || filters.cliente_id || filters.es_principal ? 'No se encontraron contactos con los filtros aplicados.' : 'Comienza agregando tu primer contacto.' }}
                    </p>
                    <div class="mt-6">
                        <Link
                            v-if="!filters.search && !filters.cliente_id && !filters.es_principal"
                            :href="route('contactos.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md border border-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Agregar Contacto
                        </Link>
                        <button
                            v-else
                            @click="clearFilters"
                            class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-100 text-sm font-medium rounded-md border border-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            <XMarkIcon class="w-4 h-4 mr-2" />
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 