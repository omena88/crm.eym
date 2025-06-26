<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { XMarkIcon, EyeIcon, PlusIcon, SunIcon, MoonIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    semana: Number,
    anio: Number,
    fechasSemana: Array
});

const emit = defineEmits(['close', 'guardado']);

const visitasPorDia = ref({});
const clientes = ref([]);
const clientesMap = ref({}); 
const isLoading = ref(false);
const showVistaDetalle = ref(false);
const visitaDetalle = ref(null);
const draggedVisita = ref(null);
const showTurnoDropdown = ref(false);

// Inicializar visitas por día
function inicializarVisitasPorDia() {
    const visitas = {};
    // Solo lunes a sábado (6 días)
    props.fechasSemana.slice(0, 6).forEach(fecha => {
        const fechaStr = fecha.toISOString().split('T')[0];
        visitas[fechaStr] = {
            mañana: [],
            tarde: []
        };
    });
    visitasPorDia.value = visitas;
}

// Cargar clientes y visitas existentes cuando el modal se monta
onMounted(async () => {
    inicializarVisitasPorDia();
    
    try {
        const response = await fetch('/clientes-list');
        if (!response.ok) throw new Error('Error al cargar clientes');
        const clientesData = await response.json();
        clientes.value = clientesData;
        
        // Crear mapa de clientes para acceso rápido
        const map = {};
        clientesData.forEach(cliente => {
            map[cliente.id] = cliente;
        });
        clientesMap.value = map;
        
        // Cargar visitas existentes de la semana
        await cargarVisitasExistentes();
    } catch (error) {
        console.error('No se pudieron cargar los datos:', error);
        alert('No se pudieron cargar los datos. Por favor, recarga la página.');
    }
});

async function cargarVisitasExistentes() {
    try {
        const response = await fetch(`/visitas/planificacion/datos?fecha=${props.fechasSemana[0].toISOString()}&vista=week&semana=${props.semana}&año=${props.anio}`);
        if (!response.ok) throw new Error('Error al cargar visitas');
        
        const data = await response.json();
        const visitasExistentes = data.visitas || [];
        
        // Distribuir visitas existentes en el formato mañana/tarde
        visitasExistentes.forEach(visita => {
            // Asegurar que usamos solo la fecha (YYYY-MM-DD)
            const fechaStr = visita.fecha_programada.split('T')[0];
            if (visitasPorDia.value[fechaStr]) {
                // Determinar si es mañana o tarde basado en la hora
                let turno = 'mañana';
                if (visita.hora_planificada) {
                    const hora = parseInt(visita.hora_planificada.split(':')[0]);
                    turno = hora >= 12 ? 'tarde' : 'mañana';
                } else if (visita.turno) {
                    turno = visita.turno;
                }
                
                visitasPorDia.value[fechaStr][turno].push({
                    id: visita.id,
                    cliente_id: visita.cliente_id,
                    fecha: fechaStr,
                    turno: turno,
                    objetivos: visita.objetivos || visita.descripcion || '',
                    tipo: visita.tipo || 'comercial',
                    estado: visita.estado || 'planificada',
                    temporal: false
                });
            }
        });
        
        console.log('Visitas existentes cargadas:', visitasPorDia.value);
    } catch (error) {
        console.error('Error cargando visitas existentes:', error);
    }
}

function agregarVisita(fechaStr) {
    const nuevaVisita = {
        id: Date.now(), // ID temporal
        cliente_id: null,
        fecha: fechaStr,
        turno: 'mañana', // Por defecto mañana
        objetivos: '',
        tipo: 'comercial',
        estado: 'planificada',
        temporal: true
    };
    
    visitasPorDia.value[fechaStr]['mañana'].push(nuevaVisita);
    
    // Abrir directamente el modal de edición
    visitaDetalle.value = nuevaVisita;
    showVistaDetalle.value = true;
}

function eliminarVisita(fechaStr, turno, visitaIndex) {
    visitasPorDia.value[fechaStr][turno].splice(visitaIndex, 1);
}

function verDetalleVisita(visita) {
    visitaDetalle.value = visita;
    showVistaDetalle.value = true;
}

function cerrarDetalle() {
    showVistaDetalle.value = false;
    visitaDetalle.value = null;
    showTurnoDropdown.value = false;
}

// Funciones de drag and drop
function onDragStart(event, visita, fechaOrigen, turnoOrigen) {
    console.log('🎯 Drag started:', visita.id, 'from', fechaOrigen, turnoOrigen);
    draggedVisita.value = { 
        visita: { ...visita }, 
        fechaOrigen: fechaOrigen,
        turnoOrigen: turnoOrigen
    };
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', JSON.stringify({ 
        visitaId: visita.id, 
        fechaOrigen: fechaOrigen,
        turnoOrigen: turnoOrigen
    }));
}

function onDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
}

function onDragEnter(event) {
    event.preventDefault();
    event.target.closest('.drop-zone')?.classList.add('drag-over');
}

function onDragLeave(event) {
    event.preventDefault();
    event.target.closest('.drop-zone')?.classList.remove('drag-over');
}

function onDrop(event, fechaDestino, turnoDestino) {
    event.preventDefault();
    console.log('📍 Drop event on:', fechaDestino, turnoDestino);
    
    // Remover clase de hover
    event.target.closest('.drop-zone')?.classList.remove('drag-over');
    
    if (!draggedVisita.value) {
        console.log('❌ No dragged visita found');
        return;
    }
    
    const { visita, fechaOrigen, turnoOrigen } = draggedVisita.value;
    console.log('🔄 Moving visita:', visita.id, 'from', fechaOrigen, turnoOrigen, 'to', fechaDestino, turnoDestino);
    
    // Si es el mismo día y turno, no hacer nada
    if (fechaOrigen === fechaDestino && turnoOrigen === turnoDestino) {
        console.log('⚠️ Same day and time, no movement needed');
        draggedVisita.value = null;
        return;
    }
    
    // Encontrar y remover del día/turno origen
    const visitasOrigen = visitasPorDia.value[fechaOrigen]?.[turnoOrigen];
    if (!visitasOrigen) {
        console.log('❌ No visitas found for origin date/time');
        draggedVisita.value = null;
        return;
    }
    
    const indexOrigen = visitasOrigen.findIndex(v => v.id === visita.id);
    if (indexOrigen === -1) {
        console.log('❌ Visita not found in origin day/time');
        draggedVisita.value = null;
        return;
    }
    
    // Remover del origen
    visitasPorDia.value[fechaOrigen][turnoOrigen].splice(indexOrigen, 1);
    console.log('✅ Removed from origin');
    
    // Agregar al destino
    const visitaMovida = { 
        ...visita, 
        fecha: fechaDestino,
        turno: turnoDestino
    };
    
    if (!visitasPorDia.value[fechaDestino]) {
        visitasPorDia.value[fechaDestino] = { mañana: [], tarde: [] };
    }
    if (!visitasPorDia.value[fechaDestino][turnoDestino]) {
        visitasPorDia.value[fechaDestino][turnoDestino] = [];
    }
    
    visitasPorDia.value[fechaDestino][turnoDestino].push(visitaMovida);
    console.log('✅ Added to destination');
    
    // Limpiar
    draggedVisita.value = null;
}

function eliminarVisitaEnModal(visitaId) {
    if (!visitaDetalle.value) return;
    
    const confirmDelete = confirm('¿Estás seguro de que quieres eliminar esta visita?');
    if (!confirmDelete) return;
    
    // Encontrar en qué día y turno está la visita
    for (const [fecha, turnos] of Object.entries(visitasPorDia.value)) {
        for (const [turno, visitas] of Object.entries(turnos)) {
            const index = visitas.findIndex(v => v.id === visitaId);
            if (index !== -1) {
                visitasPorDia.value[fecha][turno].splice(index, 1);
                cerrarDetalle();
                return;
            }
        }
    }
}

async function guardarPlanificacion() {
    // Recopilar todas las visitas de todos los días y turnos
    const todasLasVisitas = [];
    
    Object.entries(visitasPorDia.value).forEach(([fecha, turnos]) => {
        Object.entries(turnos).forEach(([turno, visitas]) => {
            visitas.forEach(visita => {
                if (visita.cliente_id && visita.objetivos.trim()) {
                    todasLasVisitas.push({
                        cliente_id: visita.cliente_id,
                        fecha_programada: fecha,
                        turno: turno,
                        objetivos: visita.objetivos,
                        tipo: visita.tipo
                    });
                }
            });
        });
    });
    
    if (todasLasVisitas.length === 0) {
        alert('Por favor agrega al menos una visita con cliente y objetivos.');
        return;
    }

    isLoading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        console.log('Enviando datos:', {
            semana: props.semana,
            año: props.anio,
            visitas: todasLasVisitas
        });
        
        const response = await fetch('/visitas/planificacion/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                semana: props.semana,
                año: props.anio,
                visitas: todasLasVisitas
            })
        });

        if (!response.ok) {
            let errorMessage = 'Error al guardar la planificación.';
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorMessage;
                console.error('Error del servidor:', errorData);
            } catch (jsonError) {
                console.error('Error parsing JSON:', jsonError);
                console.error('Response status:', response.status);
                console.error('Response text:', await response.text());
            }
            throw new Error(errorMessage);
        }

        emit('guardado');
        emit('close');
        alert(`${todasLasVisitas.length} visita(s) planificada(s) guardada(s) exitosamente`);
        
    } catch (error) {
        console.error('Error guardando planificación:', error);
        alert(error.message);
    } finally {
        isLoading.value = false;
    }
}

const formatDate = (date) => {
    const options = { weekday: 'short', day: 'numeric', month: 'short' };
    return new Date(date).toLocaleDateString('es-ES', options);
};

const getNombreCliente = (clienteId) => {
    return clientesMap.value[clienteId]?.razon_social || 'Cliente no encontrado';
};

const totalVisitas = computed(() => {
    return Object.values(visitasPorDia.value).reduce((total, turnos) => {
        return total + Object.values(turnos).reduce((subTotal, visitas) => subTotal + visitas.length, 0);
    }, 0);
});

// Watcher para mover la visita cuando se cambie el turno en el modal
watch(() => visitaDetalle.value?.turno, (nuevoTurno, turnoAnterior) => {
    if (!visitaDetalle.value || !turnoAnterior || nuevoTurno === turnoAnterior) return;
    
    const fecha = visitaDetalle.value.fecha;
    const visitaId = visitaDetalle.value.id;
    
    // Buscar y remover la visita del turno anterior
    const visitasDelTurnoAnterior = visitasPorDia.value[fecha]?.[turnoAnterior];
    if (!visitasDelTurnoAnterior) return;
    
    const index = visitasDelTurnoAnterior.findIndex(v => v.id === visitaId);
    if (index === -1) return;
    
    const visita = visitasDelTurnoAnterior.splice(index, 1)[0];
    
    // Agregar al nuevo turno
    visita.turno = nuevoTurno;
    if (!visitasPorDia.value[fecha][nuevoTurno]) {
        visitasPorDia.value[fecha][nuevoTurno] = [];
    }
    visitasPorDia.value[fecha][nuevoTurno].push(visita);
    
    console.log(`Visita movida de ${turnoAnterior} a ${nuevoTurno}`);
});

</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50" @click.self="$emit('close')">
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-7xl max-h-[95vh] flex flex-col border border-gray-700">
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <div>
                    <h3 class="text-lg font-medium text-gray-100">Planificar Visitas - Semana {{ semana }} ({{ anio }})</h3>
                    <p class="text-sm text-gray-400 mt-1">Arrastra las visitas entre días para reorganizar. Total: {{ totalVisitas }} visitas</p>
                </div>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-300">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Tablero Kanban -->
            <div class="flex-1 p-6 overflow-hidden">
                <div class="grid grid-cols-6 gap-4 h-full">
                    <div 
                        v-for="fecha in fechasSemana.slice(0, 6)" 
                        :key="fecha.toISOString()"
                        class="bg-gray-900 rounded-lg border border-gray-700 flex flex-col min-h-0"
                    >
                        <!-- Header del día -->
                        <div class="p-4 border-b border-gray-700 flex-shrink-0 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-200">{{ formatDate(fecha) }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ (visitasPorDia[fecha.toISOString().split('T')[0]]?.mañana?.length || 0) + (visitasPorDia[fecha.toISOString().split('T')[0]]?.tarde?.length || 0) }} visitas
                                </div>
                            </div>
                            <button 
                                @click="agregarVisita(fecha.toISOString().split('T')[0])"
                                class="w-8 h-8 bg-green-600 hover:bg-green-700 rounded-full flex items-center justify-center text-white transition-colors"
                                title="Agregar visita"
                            >
                                <PlusIcon class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Sección Mañana -->
                        <div class="flex-1 flex flex-col min-h-0">
                            <div class="px-4 py-2 bg-yellow-900/30 border-b border-gray-700">
                                <div class="text-xs font-medium text-yellow-300 flex items-center">
                                    <SunIcon class="w-4 h-4 mr-1" />
                                    Mañana
                                </div>
                            </div>
                            <div 
                                class="flex-1 p-3 space-y-2 overflow-y-auto min-h-0 drop-zone transition-colors"
                                @dragover.prevent="onDragOver"
                                @dragenter.prevent="onDragEnter"
                                @dragleave.prevent="onDragLeave"
                                @drop.prevent="onDrop($event, fecha.toISOString().split('T')[0], 'mañana')"
                            >
                                <div 
                                    v-for="(visita, index) in visitasPorDia[fecha.toISOString().split('T')[0]]?.mañana || []" 
                                    :key="visita.id"
                                    class="bg-blue-600 hover:bg-blue-700 rounded-lg p-3 cursor-move transition-all duration-200 group relative drag-item shadow-sm hover:shadow-md"
                                    draggable="true"
                                    @dragstart="onDragStart($event, visita, fecha.toISOString().split('T')[0], 'mañana')"
                                    @click="verDetalleVisita(visita)"
                                >
                                    <!-- Nombre del cliente -->
                                    <div class="text-sm font-medium text-white mb-1 text-left">
                                        {{ visita.cliente_id ? getNombreCliente(visita.cliente_id) : 'Sin cliente' }}
                                    </div>
                                    
                                    <!-- Objetivos (parciales) -->
                                    <div v-if="visita.objetivos" class="text-xs text-blue-100 truncate text-left">
                                        {{ visita.objetivos }}
                                    </div>
                                    
                                    <!-- Botones de acción -->
                                    <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                                        <button 
                                            @click.stop="verDetalleVisita(visita)"
                                            class="text-blue-200 hover:text-white"
                                            title="Ver detalles"
                                        >
                                            <EyeIcon class="w-3 h-3" />
                                        </button>
                                        <button 
                                            @click.stop="eliminarVisita(fecha.toISOString().split('T')[0], 'mañana', index)"
                                            class="text-red-300 hover:text-red-100"
                                            title="Eliminar"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <!-- Sección Tarde -->
                        <div class="flex-1 flex flex-col min-h-0 border-t border-gray-700">
                            <div class="px-4 py-2 bg-orange-900/30 border-b border-gray-700">
                                <div class="text-xs font-medium text-orange-300 flex items-center">
                                    <MoonIcon class="w-4 h-4 mr-1" />
                                    Tarde
                                </div>
                            </div>
                            <div 
                                class="flex-1 p-3 space-y-2 overflow-y-auto min-h-0 drop-zone transition-colors"
                                @dragover.prevent="onDragOver"
                                @dragenter.prevent="onDragEnter"
                                @dragleave.prevent="onDragLeave"
                                @drop.prevent="onDrop($event, fecha.toISOString().split('T')[0], 'tarde')"
                            >
                                <div 
                                    v-for="(visita, index) in visitasPorDia[fecha.toISOString().split('T')[0]]?.tarde || []" 
                                    :key="visita.id"
                                    class="bg-purple-600 hover:bg-purple-700 rounded-lg p-3 cursor-move transition-all duration-200 group relative drag-item shadow-sm hover:shadow-md"
                                    draggable="true"
                                    @dragstart="onDragStart($event, visita, fecha.toISOString().split('T')[0], 'tarde')"
                                    @click="verDetalleVisita(visita)"
                                >
                                    <!-- Nombre del cliente -->
                                    <div class="text-sm font-medium text-white mb-1 text-left">
                                        {{ visita.cliente_id ? getNombreCliente(visita.cliente_id) : 'Sin cliente' }}
                                    </div>
                                    
                                    <!-- Objetivos (parciales) -->
                                    <div v-if="visita.objetivos" class="text-xs text-purple-100 truncate text-left">
                                        {{ visita.objetivos }}
                                    </div>
                                    
                                    <!-- Botones de acción -->
                                    <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                                        <button 
                                            @click.stop="verDetalleVisita(visita)"
                                            class="text-purple-200 hover:text-white"
                                            title="Ver detalles"
                                        >
                                            <EyeIcon class="w-3 h-3" />
                                        </button>
                                        <button 
                                            @click.stop="eliminarVisita(fecha.toISOString().split('T')[0], 'tarde', index)"
                                            class="text-red-300 hover:text-red-100"
                                            title="Eliminar"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end p-4 border-t border-gray-700 bg-gray-800/50">
                <SecondaryButton @click="$emit('close')" class="mr-3">Cancelar</SecondaryButton>
                <PrimaryButton @click="guardarPlanificacion" :disabled="isLoading || totalVisitas === 0">
                    {{ isLoading ? 'Guardando...' : 'Guardar Planificación' }}
                </PrimaryButton>
            </div>
        </div>
    </div>

    <!-- Modal de Detalle de Visita -->
    <div v-if="showVistaDetalle" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-[60]" @click.self="cerrarDetalle" @click="showTurnoDropdown = false">
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-md border border-gray-700">
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <h4 class="text-lg font-medium text-gray-100">Detalles de Visita</h4>
                <button @click="cerrarDetalle" class="text-gray-400 hover:text-gray-300">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <div v-if="visitaDetalle" class="p-6 space-y-4">
                <!-- Cliente -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Cliente</label>
                    <select v-model="visitaDetalle.cliente_id" class="w-full bg-gray-900 border-gray-600 rounded-md text-gray-100">
                        <option :value="null" disabled>Seleccione un cliente</option>
                        <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                            {{ cliente.razon_social }}
                        </option>
                    </select>
                </div>

                <!-- Objetivos -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Objetivos</label>
                    <textarea 
                        v-model="visitaDetalle.objetivos" 
                        placeholder="Describe los objetivos de esta visita..."
                        class="w-full bg-gray-900 border-gray-600 rounded-md text-gray-100 h-24 resize-none"
                    ></textarea>
                </div>

                <!-- Turno y Tipo -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Turno</label>
                        <div class="relative" @click.stop>
                            <button 
                                @click="showTurnoDropdown = !showTurnoDropdown"
                                class="w-full bg-gray-900 border border-gray-600 rounded-md text-gray-100 px-3 py-2 text-left flex items-center justify-between hover:border-gray-500 transition-colors"
                            >
                                <div class="flex items-center">
                                    <SunIcon v-if="visitaDetalle.turno === 'mañana'" class="w-4 h-4 text-yellow-400 mr-2" />
                                    <MoonIcon v-else class="w-4 h-4 text-orange-400 mr-2" />
                                    <span>{{ visitaDetalle.turno === 'mañana' ? 'Mañana' : 'Tarde' }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div 
                                v-show="showTurnoDropdown" 
                                class="absolute z-10 w-full mt-1 bg-gray-900 border border-gray-600 rounded-md shadow-lg"
                            >
                                <button 
                                    @click="visitaDetalle.turno = 'mañana'; showTurnoDropdown = false"
                                    class="w-full px-3 py-2 text-left flex items-center hover:bg-gray-800 transition-colors text-gray-100"
                                >
                                    <SunIcon class="w-4 h-4 text-yellow-400 mr-2" />
                                    <span>Mañana</span>
                                </button>
                                <button 
                                    @click="visitaDetalle.turno = 'tarde'; showTurnoDropdown = false"
                                    class="w-full px-3 py-2 text-left flex items-center hover:bg-gray-800 transition-colors text-gray-100"
                                >
                                    <MoonIcon class="w-4 h-4 text-orange-400 mr-2" />
                                    <span>Tarde</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Tipo de Visita</label>
                        <select v-model="visitaDetalle.tipo" class="w-full bg-gray-900 border-gray-600 rounded-md text-gray-100">
                            <option value="comercial">Comercial</option>
                            <option value="soporte">Soporte</option>
                            <option value="seguimiento">Seguimiento</option>
                            <option value="cobranza">Cobranza</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-between p-4 border-t border-gray-700">
                <button 
                    @click="eliminarVisitaEnModal(visitaDetalle.id)"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors flex items-center"
                >
                    🗑️ Eliminar Visita
                </button>
                <SecondaryButton @click="cerrarDetalle">Cerrar</SecondaryButton>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Estilos para el drag and drop */
.cursor-move:hover {
    cursor: move;
}

.drag-item {
    transition: all 0.2s ease;
    user-select: none;
}

.drag-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.drag-item:active {
    cursor: grabbing;
    opacity: 0.8;
    transform: rotate(2deg) scale(1.02);
}

.drop-zone {
    transition: all 0.3s ease;
    border-radius: 8px;
}

.drop-zone.drag-over {
    background-color: rgba(59, 130, 246, 0.15) !important;
    border: 2px dashed rgba(59, 130, 246, 0.5);
    transform: scale(1.02);
}

/* Animaciones de hover mejoradas */
.drag-item:hover .group {
    opacity: 1;
}

/* Mejor spacing entre elementos */
.space-y-3 > * + * {
    margin-top: 0.75rem;
}

/* Scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #4b5563;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}

/* Mejor responsive design */
@media (max-width: 768px) {
    .grid-cols-6 {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .grid-cols-6 {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style> 