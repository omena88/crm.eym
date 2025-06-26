<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    ChevronDownIcon,
    UserIcon,
    BuildingOfficeIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    CalendarIcon,
    CheckCircleIcon,
    PlayIcon
} from '@heroicons/vue/24/outline';

// Modales
import RealizarVisitaModal from './RealizarVisitaModal.vue';
import EditarComentariosModal from './EditarComentariosModal.vue';

const props = defineProps({
    visitas: Object,
    stats: Object,
    filterOptions: Object,
    filters: Object,
    sort: Object,
    userRole: String,
    auth: Object
});

const search = ref(props.filters.search || '');
const estadoFilter = ref(props.filters.estado || '');
const tipoFilter = ref(props.filters.tipo || '');
const vendedorFilter = ref(props.filters.vendedor_id || '');

const showFilters = ref(false);
const showRealizarModal = ref(false);
const showEditarComentariosModal = ref(false);
const visitaSeleccionada = ref(null);

const filteredVisitas = computed(() => {
    return props.visitas.data;
});

const estadoColors = {
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'programada': 'bg-blue-100 text-blue-800',
    'aprobada': 'bg-green-100 text-green-800',
    'realizada': 'bg-purple-100 text-purple-800',
    'completada': 'bg-blue-100 text-blue-800',
    'cancelada': 'bg-gray-100 text-gray-800'
};

const tipoColors = {
    'planificada': 'bg-blue-100 text-blue-800',
    'no_planificada': 'bg-amber-100 text-amber-800'
};

const applyFilters = () => {
    router.get(route('visitas.index'), {
        search: search.value,
        estado: estadoFilter.value,
        tipo: tipoFilter.value,
        vendedor_id: vendedorFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    estadoFilter.value = '';
    tipoFilter.value = '';
    vendedorFilter.value = '';
    router.get(route('visitas.index'));
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatTime = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const deleteVisita = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta visita?')) {
        router.delete(route('visitas.destroy', id));
    }
};

const abrirRealizarModal = (visita) => {
    visitaSeleccionada.value = visita;
    showRealizarModal.value = true;
};

const cerrarRealizarModal = () => {
    showRealizarModal.value = false;
    visitaSeleccionada.value = null;
};

const onVisitaRealizada = () => {
    cerrarRealizarModal();
    // Recargar la página para reflejar los cambios
    router.reload();
};

const abrirEditarComentariosModal = (visita) => {
    visitaSeleccionada.value = visita;
    showEditarComentariosModal.value = true;
};

const cerrarEditarComentariosModal = () => {
    showEditarComentariosModal.value = false;
    visitaSeleccionada.value = null;
};

const onComentariosActualizados = () => {
    cerrarEditarComentariosModal();
    // Recargar la página para reflejar los cambios
    router.reload();
};

const ejecutarVisitaPlanificada = async (visita) => {
    if (confirm('¿Deseas cambiar esta visita planificada a ejecutada?')) {
        try {
            await router.post(`/visitas/${visita.id}/ejecutar`, {}, {
                onSuccess: () => {
                    router.reload();
                },
                onError: (errors) => {
                    console.error('Error ejecutando visita:', errors);
                    alert('Error al ejecutar la visita');
                }
            });
        } catch (error) {
            console.error('Error:', error);
            alert('Error al ejecutar la visita');
        }
    }
};

const puedeRealizar = (visita) => {
    return ['programada', 'aprobada'].includes(visita.estado) && 
           props.userRole === 'vendedor' &&
           visita.user_id === props.auth.user.id;
};

const puedeEjecutarPlanificada = (visita) => {
    return visita.estado === 'programada' && 
           (visita.tipo_planificacion === 'planificada' || !visita.tipo_planificacion) &&
           props.userRole === 'vendedor' &&
           visita.user_id === props.auth.user.id;
};

const puedeEditar = (visita) => {
    return ['pendiente', 'rechazada'].includes(visita.estado) && 
           props.userRole === 'vendedor' &&
           visita.user_id === props.auth.user.id;
};

const puedeEditarComentarios = (visita) => {
    return ['programada', 'aprobada', 'realizada'].includes(visita.estado) && 
           props.userRole === 'vendedor' &&
           visita.user_id === props.auth.user.id;
};

const puedeEliminar = (visita) => {
    return ['pendiente', 'rechazada'].includes(visita.estado) && 
           props.userRole === 'vendedor' &&
           visita.user_id === props.auth.user.id;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-100">{{ stats.total }}</p>
                    <p class="text-sm text-gray-400">Total</p>
                </div>
            </div>
            
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.pendientes }}</p>
                    <p class="text-sm text-gray-400">Pendientes</p>
                </div>
            </div>
            
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-400">{{ stats.programadas || 0 }}</p>
                    <p class="text-sm text-gray-400">Programadas</p>
                </div>
            </div>
            
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-400">{{ stats.aprobadas }}</p>
                    <p class="text-sm text-gray-400">Aprobadas</p>
                </div>
            </div>
            
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-purple-400">{{ stats.realizadas || stats.completadas }}</p>
                    <p class="text-sm text-gray-400">Realizadas</p>
                </div>
            </div>
            
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-300">{{ stats.planificadas }}</p>
                    <p class="text-sm text-gray-400">Planificadas</p>
                </div>
            </div>
            
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-amber-400">{{ stats.no_planificadas }}</p>
                    <p class="text-sm text-gray-400">No Planificadas</p>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex-1 lg:max-w-md">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Buscar por título o cliente..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <button
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-md border border-gray-700 transition-colors"
                    >
                        <FunnelIcon class="w-4 h-4 mr-2" />
                        Filtros
                        <ChevronDownIcon 
                            class="w-4 h-4 ml-2 transition-transform"
                            :class="{ 'transform rotate-180': showFilters }"
                        />
                    </button>
                    
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors"
                    >
                        Buscar
                    </button>
                </div>
            </div>

            <!-- Filtros expandibles -->
            <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-800">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Estado</label>
                        <select
                            v-model="estadoFilter"
                            class="w-full bg-gray-800 border border-gray-700 rounded-md text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todos los estados</option>
                            <option v-for="estado in filterOptions.estados" :key="estado" :value="estado">
                                {{ estado.charAt(0).toUpperCase() + estado.slice(1) }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Tipo</label>
                        <select
                            v-model="tipoFilter"
                            class="w-full bg-gray-800 border border-gray-700 rounded-md text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todos los tipos</option>
                            <option v-for="tipo in filterOptions.tipos" :key="tipo" :value="tipo">
                                {{ tipo === 'planificada' ? 'Planificada' : 'No Planificada' }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="userRole === 'gerente'">
                        <label class="block text-sm font-medium text-gray-400 mb-2">Vendedor</label>
                        <select
                            v-model="vendedorFilter"
                            class="w-full bg-gray-800 border border-gray-700 rounded-md text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todos los vendedores</option>
                            <option v-for="vendedor in filterOptions.vendedores" :key="vendedor.id" :value="vendedor.id">
                                {{ vendedor.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium rounded-md transition-colors"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de visitas -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-800">
                    <thead class="bg-gray-950">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th v-if="userRole === 'gerente'" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Vendedor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-900 divide-y divide-gray-800">
                        <tr v-for="visita in filteredVisitas" :key="visita.id" class="hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <BuildingOfficeIcon class="w-4 h-4 text-gray-400 mr-2" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-100">
                                            {{ visita.cliente.razon_social }}
                                        </div>
                                        <div v-if="visita.cliente.nombre_comercial" class="text-sm text-gray-400">
                                            {{ visita.cliente.nombre_comercial }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td v-if="userRole === 'gerente'" class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <UserIcon class="w-4 h-4 text-gray-400 mr-2" />
                                    <span class="text-sm text-gray-100">{{ visita.usuario?.name || 'N/A' }}</span>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <CalendarIcon class="w-4 h-4 text-gray-400 mr-2" />
                                    <div>
                                        <div class="text-sm text-gray-100">{{ formatDate(visita.fecha_programada) }}</div>
                                        <div v-if="visita.hora_planificada" class="text-sm text-gray-400">
                                            {{ formatTime(visita.hora_planificada) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="estadoColors[visita.estado] || 'bg-gray-100 text-gray-800'"
                                >
                                    {{ visita.estado.charAt(0).toUpperCase() + visita.estado.slice(1) }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="tipoColors[visita.tipo_planificacion || 'planificada'] || 'bg-gray-100 text-gray-800'"
                                    >
                                        {{ (visita.tipo_planificacion || 'planificada') === 'planificada' ? 'Planificada' : 'No Planificada' }}
                                    </span>
                                    <div class="text-xs text-gray-400">
                                        {{ visita.tipo ? visita.tipo.charAt(0).toUpperCase() + visita.tipo.slice(1) : 'N/A' }}
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- Ver -->
                                    <Link
                                        :href="route('visitas.show', visita.id)"
                                        class="text-blue-400 hover:text-blue-300 transition-colors"
                                        title="Ver detalles"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                    </Link>
                                    
                                    <!-- Ejecutar Planificada -->
                                    <button
                                        v-if="puedeEjecutarPlanificada(visita)"
                                        @click="ejecutarVisitaPlanificada(visita)"
                                        class="text-blue-400 hover:text-blue-300 transition-colors"
                                        title="Cambiar a ejecutada"
                                    >
                                        <CheckCircleIcon class="w-4 h-4" />
                                    </button>
                                    
                                    <!-- Realizar -->
                                    <button
                                        v-if="puedeRealizar(visita)"
                                        @click="abrirRealizarModal(visita)"
                                        class="text-green-400 hover:text-green-300 transition-colors"
                                        title="Realizar visita"
                                    >
                                        <PlayIcon class="w-4 h-4" />
                                    </button>
                                    
                                    <!-- Editar -->
                                    <Link
                                        v-if="puedeEditar(visita)"
                                        :href="route('visitas.edit', visita.id)"
                                        class="text-yellow-400 hover:text-yellow-300 transition-colors"
                                        title="Editar"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </Link>
                                    
                                    <!-- Editar Comentarios -->
                                    <button
                                        v-if="puedeEditarComentarios(visita)"
                                        @click="abrirEditarComentariosModal(visita)"
                                        class="text-yellow-400 hover:text-yellow-300 transition-colors"
                                        title="Editar comentarios"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    
                                    <!-- Eliminar -->
                                    <button
                                        v-if="puedeEliminar(visita)"
                                        @click="deleteVisita(visita.id)"
                                        class="text-red-400 hover:text-red-300 transition-colors"
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
            <div v-if="visitas.links && visitas.links.length > 3" class="bg-gray-950 px-4 py-3 border-t border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="visitas.prev_page_url"
                            :href="visitas.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-700 text-sm font-medium rounded-md text-gray-300 bg-gray-800 hover:bg-gray-700"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="visitas.next_page_url"
                            :href="visitas.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-700 text-sm font-medium rounded-md text-gray-300 bg-gray-800 hover:bg-gray-700"
                        >
                            Siguiente
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-400">
                                Mostrando
                                <span class="font-medium">{{ visitas.from }}</span>
                                a
                                <span class="font-medium">{{ visitas.to }}</span>
                                de
                                <span class="font-medium">{{ visitas.total }}</span>
                                resultados
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <template v-for="(link, index) in visitas.links">
                                    <Link
                                        v-if="link.url"
                                        :key="index"
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            link.active
                                                ? 'z-10 bg-blue-600 border-blue-600 text-white'
                                                : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === visitas.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                    />
                                    <span
                                        v-else
                                        :key="'span-' + index"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-700 bg-gray-800 text-sm font-medium text-gray-500 cursor-default"
                                        :class="[
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === visitas.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                    ></span>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para realizar visita -->
    <RealizarVisitaModal
        v-if="showRealizarModal && visitaSeleccionada"
        :visita="visitaSeleccionada"
        @close="cerrarRealizarModal"
        @visitaRealizada="onVisitaRealizada"
    />

    <!-- Modal para editar comentarios -->
    <EditarComentariosModal
        v-if="showEditarComentariosModal && visitaSeleccionada"
        :visita="visitaSeleccionada"
        @close="cerrarEditarComentariosModal"
        @updated="onComentariosActualizados"
    />
</template> 