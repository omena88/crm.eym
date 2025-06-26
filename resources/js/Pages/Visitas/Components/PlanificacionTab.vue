<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    CalendarDaysIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    PaperAirplaneIcon,
    XMarkIcon,
    ClipboardDocumentListIcon,
    SunIcon,
    MoonIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';
import PlanificarDiaModal from './PlanificarDiaModal.vue';
import PlanificacionVisitasModal from './PlanificacionVisitasModal.vue';

const props = defineProps({
    userRole: String,
    auth: Object
});

const page = usePage();

// Estado del calendario
const currentDate = ref(new Date());
const visitas = ref([]);
const loading = ref(false);

// Estado de planificación
const semanaActual = ref(getCurrentWeek());
const añoActual = ref(new Date().getFullYear());
const planificacionEnviada = ref(false);
const showModalDia = ref(false);
const fechaSeleccionada = ref(null);
const showModalDetalle = ref(false);
const visitaSeleccionada = ref(null);
const showModalKanban = ref(false);

// Computed properties
const fechasVista = computed(() => {
    try {
        if (!currentDate.value) {
            return [];
        }
        return getWeekDates(currentDate.value);
    } catch (error) {
        console.error('Error obteniendo fechas de vista:', error);
        return [];
    }
});

const visitasDelPeriodo = computed(() => {
    return visitas.value.filter(visita => {
        const fechaVisita = new Date(visita.fecha_programada);
        const fechas = fechasVista.value;
        return fechaVisita >= fechas[0] && fechaVisita <= fechas[fechas.length - 1];
    });
});

const puedeEnviarPlanificacion = computed(() => {
    return props.userRole === 'vendedor' && 
           visitasDelPeriodo.value.length > 0 && 
           !planificacionEnviada.value;
});

// Métodos
function getCurrentWeek() {
    const today = new Date();
    const firstDay = new Date(today.setDate(today.getDate() - today.getDay() + 1));
    return getWeekNumber(firstDay);
}

function getWeekNumber(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

function getWeekDates(date) {
    const startOfWeek = new Date(date);
    const day = startOfWeek.getDay();
    const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1);
    startOfWeek.setDate(diff);
    
    const dates = [];
    // Solo lunes a sábado (6 días, no 7)
    for (let i = 0; i < 6; i++) {
        const currentDate = new Date(startOfWeek);
        currentDate.setDate(startOfWeek.getDate() + i);
        dates.push(currentDate);
    }
    return dates;
}

function navegarPeriodo(direccion) {
    const newDate = new Date(currentDate.value);
    newDate.setDate(newDate.getDate() + (direccion * 7));
    currentDate.value = newDate;
    
    // Actualizar semana y año actuales
    semanaActual.value = getWeekNumber(currentDate.value);
    añoActual.value = currentDate.value.getFullYear();
    
    cargarVisitas();
}

function cambiarVistaHoy() {
    currentDate.value = new Date();
    
    // Actualizar semana y año actuales
    semanaActual.value = getWeekNumber(currentDate.value);
    añoActual.value = currentDate.value.getFullYear();
    
    cargarVisitas();
}

async function cargarVisitas() {
    loading.value = true;
    try {
        const response = await fetch(`/visitas/planificacion/datos?fecha=${currentDate.value.toISOString()}&vista=week&semana=${semanaActual.value}&año=${añoActual.value}`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        visitas.value = data.visitas || [];
        // Actualizar el estado de aprobación para la semana corriente
        planificacionEnviada.value = data.planificacionEnviada || false;
        console.log('Datos cargados (sincronizado):', data);
        
        // Forzar actualización del componente para reflejar cambios
        // desde el modal de planificación
        if (data.visitas) {
            console.log('Visitas actualizadas para sincronización:', data.visitas.length);
        }
    } catch (error) {
        console.error('Error cargando visitas:', error);
        visitas.value = [];
        planificacionEnviada.value = false;
        // Mostrar un mensaje de error más amigable al usuario
        alert('Error al cargar las visitas. Por favor, recarga la página.');
    } finally {
        loading.value = false;
    }
}

function getVisitasDelDia(fecha) {
    return visitasDelPeriodo.value.filter(visita => {
        // Usar solo la parte de fecha (YYYY-MM-DD) para evitar problemas de zona horaria
        const fechaVisitaStr = visita.fecha_programada.split('T')[0]; 
        const fechaComparar = fecha.toISOString().split('T')[0];
        return fechaVisitaStr === fechaComparar;
    });
}

function formatearFecha(fecha) {
    try {
        if (!fecha || !(fecha instanceof Date)) {
            return 'Fecha inválida';
        }
        return fecha.toLocaleDateString('es-ES', {
            weekday: 'short',
            day: 'numeric',
            month: 'short'
        });
    } catch (error) {
        console.error('Error formateando fecha:', error, fecha);
        return 'Error';
    }
}

function formatearSemana() {
    try {
        if (!currentDate.value) {
            return 'Cargando...';
        }
        
        const fechas = getWeekDates(currentDate.value);
        if (!fechas || fechas.length < 6) {
            return 'Error en fechas';
        }
        
        const inicio = fechas[0]?.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        const fin = fechas[5]?.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        
        if (!inicio || !fin) {
            return 'Error en formateo';
        }
        
        return `${inicio} - ${fin}, ${currentDate.value.getFullYear()}`;
    } catch (error) {
        console.error('Error formateando semana:', error);
        return 'Error en calendario';
    }
}

function abrirModalDia(fecha) {
    fechaSeleccionada.value = fecha;
    showModalDia.value = true;
}

function cerrarModalDia() {
    showModalDia.value = false;
}

function visitasGuardadas() {
    // Esta función se llamará cuando el modal de planificación por día se guarde exitosamente
    // para refrescar la lista de visitas.
    console.log('🔄 Recargando visitas tras guardado...');
    cargarVisitas();
}

function verDetalleVisita(visita) {
    visitaSeleccionada.value = visita;
    showModalDetalle.value = true;
}

function cerrarModalDetalle() {
    showModalDetalle.value = false;
    visitaSeleccionada.value = null;
}

function abrirModalKanban() {
    showModalKanban.value = true;
}

function cerrarModalKanban() {
    showModalKanban.value = false;
}

async function enviarPlanificacion() {
    if (!puedeEnviarPlanificacion.value) return;
    
    if (!confirm('¿Estás seguro de enviar esta planificación para aprobación?')) return;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('/visitas/enviar-planificacion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                semana: semanaActual.value,
                año: añoActual.value
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Planificación enviada exitosamente para aprobación');
            planificacionEnviada.value = true;
            cargarVisitas();
        } else {
            alert(data.message || 'Error al enviar la planificación');
        }
    } catch (error) {
        console.error('Error enviando planificación:', error);
        alert('Error al enviar la planificación. Por favor, inténtalo de nuevo.');
    }
}

async function revertirPlanificacion() {
    if (!confirm('¿Estás seguro de solicitar la reversión de esta planificación? Se enviará una solicitud al gerente.')) return;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('/visitas/revertir-planificacion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                semana: semanaActual.value,
                año: añoActual.value
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Solicitud de reversión enviada al gerente exitosamente');
            planificacionEnviada.value = false;
            cargarVisitas();
        } else {
            alert(data.message || 'Error al solicitar la reversión');
        }
    } catch (error) {
        console.error('Error solicitando reversión:', error);
        alert('Error al solicitar la reversión. Por favor, inténtalo de nuevo.');
    }
}

// Cargar datos al montar
onMounted(() => {
    cargarVisitas();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header con controles -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <!-- Navegación de fecha -->
                <div class="flex items-center space-x-4">
                    <button
                        @click="navegarPeriodo(-1)"
                        class="p-2 text-gray-400 hover:text-gray-200 hover:bg-gray-800 rounded-md transition-colors"
                    >
                        <ChevronLeftIcon class="w-5 h-5" />
                    </button>
                    
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-100">
                            {{ formatearSemana() }}
                        </h3>
                        <p class="text-sm text-gray-400">
                            Semana {{ semanaActual }}
                        </p>
                    </div>
                    
                    <button
                        @click="navegarPeriodo(1)"
                        class="p-2 text-gray-400 hover:text-gray-200 hover:bg-gray-800 rounded-md transition-colors"
                    >
                        <ChevronRightIcon class="w-5 h-5" />
                    </button>
                </div>
                
                <!-- Controles de vista -->
                <div class="flex items-center space-x-2">
                    <button
                        @click="cambiarVistaHoy"
                        class="px-3 py-2 text-sm text-blue-400 hover:text-blue-300 transition-colors"
                    >
                        Hoy
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Herramientas de planificación -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">            
            <div class="flex flex-wrap items-center gap-4">
                <!-- Planificar Semana -->
                <button
                    @click="abrirModalKanban"
                    :disabled="planificacionEnviada && userRole === 'vendedor'"
                    :class="[
                        'flex items-center justify-center px-4 py-3 rounded-md transition-colors',
                        (planificacionEnviada && userRole === 'vendedor')
                            ? 'bg-gray-700 text-gray-400 cursor-not-allowed'
                            : 'bg-blue-600 hover:bg-blue-700 text-white'
                    ]"
                >
                    <CalendarDaysIcon class="w-5 h-5 mr-2" />
                    Planificar semana
                </button>
                
                <!-- Enviar/Revertir planificación (solo para vendedores) -->
                <button
                    v-if="userRole === 'vendedor'"
                    @click="planificacionEnviada ? revertirPlanificacion() : enviarPlanificacion()"
                    :disabled="!planificacionEnviada && !puedeEnviarPlanificacion"
                    :class="[
                        'flex items-center justify-center px-4 py-3 rounded-md transition-colors',
                        planificacionEnviada
                            ? 'bg-orange-600 hover:bg-orange-700 text-white'
                            : puedeEnviarPlanificacion
                                ? 'bg-green-600 hover:bg-green-700 text-white'
                                : 'bg-gray-700 text-gray-400 cursor-not-allowed'
                    ]"
                >
                    <PaperAirplaneIcon class="w-5 h-5 mr-2" />
                    {{ planificacionEnviada ? 'Solicitar Reversión' : 'Enviar a Aprobación' }}
                </button>

                <!-- Información del estado para roles superiores -->
                <div v-if="userRole !== 'vendedor'" class="flex items-center space-x-2 text-sm text-gray-400">
                    <span>Vista de {{ userRole === 'gerente' ? 'Gerente' : userRole === 'admin' ? 'Administrador' : 'Supervisor' }}</span>
                </div>
            </div>
            
            <!-- Estado de planificación -->
            <div v-if="planificacionEnviada && userRole === 'vendedor'" class="mt-4 p-3 bg-green-900/50 border border-green-700 rounded-md">
                <p class="text-sm text-green-300">
                    ✅ La planificación de esta semana ya fue enviada para aprobación del gerente.
                </p>
            </div>

            <!-- Información para gerentes/admin -->
            <div v-if="userRole !== 'vendedor' && visitasDelPeriodo.length > 0" class="mt-4 p-3 bg-blue-900/50 border border-blue-700 rounded-md">
                <p class="text-sm text-blue-300">
                    📋 Visualizando planificación de todos los vendedores. Puedes planificar visitas adicionales si es necesario.
                </p>
            </div>
        </div>
        
        <!-- Calendario de planificación -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-400">
                Cargando calendario...
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 lg:gap-0 p-4 lg:p-0">
                <div 
                    v-for="(fecha, index) in fechasVista" 
                    :key="index"
                    class="bg-gray-800/50 lg:bg-transparent border-r border-gray-700 last:border-r-0 p-4 min-h-[350px] flex flex-col"
                >
                    <!-- Header del día -->
                    <div class="border-b border-gray-700 pb-3 mb-4">
                        <h3 class="font-semibold text-gray-200 text-sm capitalize">
                            {{ formatearFecha(fecha) }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ fecha ? fecha.toLocaleDateString('es-ES', { day: 'numeric', month: 'long' }) : 'Cargando...' }}
                        </p>
                    </div>
                    
                    <!-- Contenedor para Mañana y Tarde -->
                    <div class="flex-grow space-y-3">
                        <!-- Turno Mañana -->
                        <div
                            class="bg-yellow-900/20 rounded-lg p-3 cursor-pointer hover:bg-yellow-900/30 transition-colors border border-yellow-700/30"
                            @click="abrirModalDia(fecha)"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="flex items-center text-xs font-bold text-yellow-400">
                                    <SunIcon class="w-4 h-4 mr-2" />
                                    Mañana
                                </h4>
                                <span class="text-xs text-gray-400">
                                    {{ getVisitasDelDia(fecha).filter(v => v.turno === 'mañana').length }}
                                </span>
                            </div>
                            <div class="space-y-2 min-h-[60px]">
                                <div
                                    v-for="visita in getVisitasDelDia(fecha).filter(v => v.turno === 'mañana')"
                                    :key="visita.id"
                                    @click.stop="verDetalleVisita(visita)"
                                    class="bg-blue-600 hover:bg-blue-700 p-2 rounded text-white text-xs transition-colors cursor-pointer"
                                >
                                    <p class="font-medium truncate">{{ visita.cliente.razon_social }}</p>
                                    <p class="text-blue-100 truncate">{{ visita.titulo }}</p>
                                    <p v-if="userRole !== 'vendedor' && visita.usuario" class="text-blue-200 text-xs">
                                        👤 {{ visita.usuario.name }}
                                    </p>
                                </div>
                                <div v-if="getVisitasDelDia(fecha).filter(v => v.turno === 'mañana').length === 0" 
                                     class="text-center py-2 text-gray-500 text-xs">
                                    Sin visitas de mañana
                                </div>
                            </div>
                        </div>
                        
                        <!-- Turno Tarde -->
                        <div
                            class="bg-indigo-900/20 rounded-lg p-3 cursor-pointer hover:bg-indigo-900/30 transition-colors border border-indigo-700/30"
                            @click="abrirModalDia(fecha)"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="flex items-center text-xs font-bold text-indigo-400">
                                    <MoonIcon class="w-4 h-4 mr-2" />
                                    Tarde
                                </h4>
                                <span class="text-xs text-gray-400">
                                    {{ getVisitasDelDia(fecha).filter(v => v.turno === 'tarde').length }}
                                </span>
                            </div>
                            <div class="space-y-2 min-h-[60px]">
                                <div
                                    v-for="visita in getVisitasDelDia(fecha).filter(v => v.turno === 'tarde')"
                                    :key="visita.id"
                                    @click.stop="verDetalleVisita(visita)"
                                    class="bg-purple-600 hover:bg-purple-700 p-2 rounded text-white text-xs transition-colors cursor-pointer"
                                >
                                    <p class="font-medium truncate">{{ visita.cliente.razon_social }}</p>
                                    <p class="text-purple-100 truncate">{{ visita.titulo }}</p>
                                    <p v-if="userRole !== 'vendedor' && visita.usuario" class="text-purple-200 text-xs">
                                        👤 {{ visita.usuario.name }}
                                    </p>
                                </div>
                                <div v-if="getVisitasDelDia(fecha).filter(v => v.turno === 'tarde').length === 0" 
                                     class="text-center py-2 text-gray-500 text-xs">
                                    Sin visitas de tarde
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen de visitas del período -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-100 mb-4">
                Resumen de la Semana
            </h4>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-400">{{ visitasDelPeriodo.length }}</div>
                    <div class="text-sm text-gray-400">Total Visitas</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-400">
                        {{ visitasDelPeriodo.filter(v => v.estado === 'programada').length }}
                    </div>
                    <div class="text-sm text-gray-400">Programadas</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-400">
                        {{ visitasDelPeriodo.filter(v => v.estado === 'pendiente').length }}
                    </div>
                    <div class="text-sm text-gray-400">En Aprobación</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-400">
                        {{ visitasDelPeriodo.filter(v => v.estado === 'aprobada').length }}
                    </div>
                    <div class="text-sm text-gray-400">Aprobadas por Gerente</div>
                </div>
            </div>
        </div>

        <!-- Modal de Planificación por Día -->
        <PlanificarDiaModal
            v-if="showModalDia"
            :show="showModalDia"
            :fecha="fechaSeleccionada"
            :semana="semanaActual"
            :anio="añoActual"
            @close="cerrarModalDia"
            @guardado="visitasGuardadas"
        />

        <!-- Modal Kanban de Planificación Semanal -->
        <PlanificacionVisitasModal
            v-if="showModalKanban"
            :show="showModalKanban"
            :semana="semanaActual"
            :anio="añoActual"
            :fechas-semana="fechasVista"
            @close="cerrarModalKanban"
            @guardado="visitasGuardadas"
        />

        <!-- Modal de Detalle de Visita -->
        <div v-if="showModalDetalle" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50" @click.self="cerrarModalDetalle">
            <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-md border border-gray-700">
                <div class="flex items-center justify-between p-4 border-b border-gray-700">
                    <h4 class="text-lg font-medium text-gray-100">Detalles de la Visita</h4>
                    <button @click="cerrarModalDetalle" class="text-gray-400 hover:text-gray-300">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="visitaSeleccionada" class="p-6">
                    <div class="space-y-4">
                        <!-- Cliente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Cliente</label>
                            <div class="text-gray-100 bg-gray-900 rounded-md px-3 py-2">
                                {{ visitaSeleccionada.cliente?.razon_social || 'Sin cliente' }}
                            </div>
                        </div>

                        <!-- Vendedor (para gerentes/admin) -->
                        <div v-if="userRole !== 'vendedor' && visitaSeleccionada.usuario">
                            <label class="block text-sm font-medium text-gray-400 mb-1">Vendedor</label>
                            <div class="text-gray-100 bg-gray-900 rounded-md px-3 py-2">
                                {{ visitaSeleccionada.usuario.name }}
                            </div>
                        </div>

                        <!-- Fecha y Turno -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Fecha</label>
                                <div class="text-gray-100 bg-gray-900 rounded-md px-3 py-2">
                                    {{ visitaSeleccionada.fecha_programada.split('T')[0] }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Turno</label>
                                <div class="text-gray-100 bg-gray-900 rounded-md px-3 py-2 capitalize">
                                    {{ visitaSeleccionada.turno || 'No especificado' }}
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Estado</label>
                            <div class="flex items-center space-x-2">
                                <div 
                                    :class="[
                                        'w-3 h-3 rounded-full',
                                        visitaSeleccionada.estado === 'programada' ? 'bg-orange-400' :
                                        visitaSeleccionada.estado === 'pendiente' ? 'bg-yellow-400' :
                                        visitaSeleccionada.estado === 'aprobada' ? 'bg-green-400' :
                                        'bg-gray-400'
                                    ]"
                                ></div>
                                <span class="text-gray-100 capitalize">
                                    {{ 
                                        visitaSeleccionada.estado === 'programada' ? 'Programada' :
                                        visitaSeleccionada.estado === 'pendiente' ? 'En Aprobación' :
                                        visitaSeleccionada.estado === 'aprobada' ? 'Aprobada por Gerente' :
                                        visitaSeleccionada.estado 
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Tipo de Visita</label>
                            <div class="text-gray-100 bg-gray-900 rounded-md px-3 py-2 capitalize">
                                {{ visitaSeleccionada.tipo || 'No especificado' }}
                            </div>
                        </div>

                        <!-- Objetivos -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Objetivos</label>
                            <div class="text-gray-100 bg-gray-900 rounded-md px-3 py-2 min-h-[60px]">
                                {{ visitaSeleccionada.objetivos || 'Sin objetivos definidos' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end p-4 border-t border-gray-700">
                    <button 
                        @click="cerrarModalDetalle"
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition-colors"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ... */
</style> 