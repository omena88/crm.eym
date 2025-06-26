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
    DocumentArrowDownIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    BuildingOfficeIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';
import {
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    clientes: Object,
    filters: Object,
    estados: Array,
    sectores: Array,
});

// Filtros
const filters = ref({
    search: props.filters.search || '',
    estado: props.filters.estado || '',
    sector: props.filters.sector || '',
});

const showFilters = ref(false);
const importModal = ref(false);

// Formulario de importación
const importForm = useForm({
    file: null,
});

// Observar cambios en filtros con debounce para búsqueda en tiempo real
let searchTimeout;
watch(filters, (value) => {
    // Para la búsqueda, usar debounce para evitar muchas requests
    if (searchTimeout) clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        router.get(route('clientes.index'), value, {
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
        estado: '',
        sector: '',
    };
};

// Función para exportar
const exportClientes = () => {
    window.location.href = route('clientes.export', filters.value);
};

// Función para descargar template
const downloadTemplate = () => {
    window.location.href = route('clientes.template');
};

// Función para importar
const importClientes = () => {
    importForm.post(route('clientes.import'), {
        onSuccess: () => {
            importModal.value = false;
            importForm.reset();
        },
        onError: (errors) => {
            console.error('Error en importación:', errors);
        }
    });
};

// Función para obtener color del estado
const getEstadoBadgeColor = (estado) => {
    const colors = {
        'potencial': 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'visitado': 'bg-blue-100 text-blue-800 border-blue-300',
        'cotizado': 'bg-purple-100 text-purple-800 border-purple-300',
        'cliente': 'bg-green-100 text-green-800 border-green-300',
        'inactivo': 'bg-gray-100 text-gray-800 border-gray-300',
    };
    return colors[estado] || 'bg-gray-100 text-gray-800 border-gray-300';
};

// Función para eliminar cliente
const deleteCliente = (cliente) => {
    if (confirm(`¿Estás seguro de que deseas eliminar el cliente "${cliente.razon_social}"?`)) {
        router.delete(route('clientes.destroy', cliente.id));
    }
};
</script>

<template>
    <Head title="Clientes" />

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
                    :href="route('clientes.create')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md border border-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Nuevo Cliente
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                                    placeholder="Buscar por razón social, RUC o sector..."
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
                                <span v-if="filters.estado || filters.sector" class="ml-2 px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full">
                                    {{ (filters.estado ? 1 : 0) + (filters.sector ? 1 : 0) }}
                                </span>
                            </button>

                            <button
                                @click="exportClientes"
                                class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md border border-green-500 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                                <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                                Exportar
                            </button>

                            <button
                                @click="importModal = true"
                                class="inline-flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md border border-purple-500 transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500"
                            >
                                <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
                                Importar
                            </button>
                        </div>
                    </div>

                    <!-- Filtros expandibles -->
                    <div v-if="showFilters" class="border-t border-gray-800 pt-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Estado</label>
                                <select
                                    v-model="filters.estado"
                                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                >
                                    <option value="">Todos los estados</option>
                                    <option v-for="estado in estados" :key="estado" :value="estado">
                                        {{ estado }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Sector</label>
                                <select
                                    v-model="filters.sector" 
                                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                >
                                    <option value="">Todos los sectores</option>
                                    <option v-for="sector in sectores" :key="sector" :value="sector">
                                        {{ sector }}
                                    </option>
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

                <!-- Tabla de clientes -->
                <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-800">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Cliente / Vendedor
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Contacto Principal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Sector
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-900 divide-y divide-gray-800">
                                <tr v-for="cliente in clientes.data" :key="cliente.id" class="hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-100">
                                                {{ cliente.razon_social }}
                                            </div>
                                            <div class="text-sm text-gray-400">
                                                RUC: {{ cliente.ruc }}
                                            </div>
                                            <div v-if="cliente.vendedor" class="text-xs text-gray-500 mt-1">
                                                Vendedor: {{ cliente.vendedor.name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="cliente.contacto_principal">
                                            <div class="text-sm font-medium text-gray-100">
                                                {{ cliente.contacto_principal.nombre }} {{ cliente.contacto_principal.apellidos }}
                                            </div>
                                            <div v-if="cliente.contacto_principal.puesto" class="text-sm text-gray-400">
                                                {{ cliente.contacto_principal.puesto }}
                                            </div>
                                        </div>
                                        <div v-else class="text-sm text-gray-500">
                                            Sin contacto principal
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-200 border border-gray-600">
                                            {{ cliente.sector }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getEstadoBadgeColor(cliente.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border">
                                            {{ cliente.estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link
                                                :href="route('clientes.show', cliente.id)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200"
                                                title="Ver"
                                            >
                                                <EyeIcon class="w-4 h-4" />
                                            </Link>
                                            <Link
                                                :href="route('clientes.edit', cliente.id)"
                                                class="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
                                                title="Editar"
                                            >
                                                <PencilIcon class="w-4 h-4" />
                                            </Link>
                                            <Link
                                                :href="route('clientes.historial', cliente.id)"
                                                class="text-teal-600 hover:text-teal-900 dark:text-teal-400 dark:hover:text-teal-200"
                                                title="Historial"
                                            >
                                                <ClockIcon class="h-5 w-5" />
                                            </Link>
                                            <button
                                                @click="deleteCliente(cliente)"
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
                    <div v-if="clientes.links.length > 3" class="bg-gray-800 px-4 py-3 border-t border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-400">
                                Mostrando {{ clientes.from }} - {{ clientes.to }} de {{ clientes.total }} clientes
                            </div>
                            <nav class="flex items-center space-x-2">
                                <component
                                    v-for="(link, index) in clientes.links"
                                    :key="index"
                                    :is="link.url ? 'Link' : 'span'"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="{
                                        'bg-blue-600 text-white border border-blue-500': link.active,
                                        'bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600': !link.active && link.url,
                                        'bg-gray-700 text-gray-500 border border-gray-600 cursor-not-allowed': !link.url
                                    }"
                                />
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay clientes -->
                <div v-if="clientes.data.length === 0" class="text-center py-12">
                    <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-500" />
                    <h3 class="mt-2 text-lg font-medium text-gray-100">No hay clientes</h3>
                    <p class="mt-1 text-gray-400">
                        {{ filters.search || filters.estado || filters.sector ? 'No se encontraron clientes con los filtros aplicados.' : 'Comienza agregando tu primer cliente.' }}
                    </p>
                    <div class="mt-6">
                        <Link
                            v-if="!filters.search && !filters.estado && !filters.sector"
                            :href="route('clientes.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md border border-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Agregar Cliente
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
            </div>
        </div>

        <!-- Modal de importación -->
        <div v-if="importModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-100">Importar Clientes</h3>
                    <button
                        @click="importModal = false"
                        class="text-gray-400 hover:text-gray-300 transition-colors"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-400 mb-3">
                        Descarga primero el template con el formato correcto:
                    </p>
                    <button
                        @click="downloadTemplate"
                        class="inline-flex items-center px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-100 text-sm font-medium rounded-md border border-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
                        Descargar Template
                    </button>
                </div>

                <form @submit.prevent="importClientes">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Seleccionar archivo CSV
                        </label>
                        <input
                            type="file"
                            accept=".csv,.txt"
                            @change="importForm.file = $event.target.files[0]"
                            class="block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors file:mr-4 file:py-1 file:px-3 file:border-0 file:text-sm file:font-medium file:bg-blue-600 file:text-white file:rounded-md file:cursor-pointer hover:file:bg-blue-700"
                        />
                        <div v-if="importForm.errors.file" class="mt-1 text-sm text-red-400">
                            {{ importForm.errors.file }}
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="importModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="!importForm.file || importForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-500 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ importForm.processing ? 'Importando...' : 'Importar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 