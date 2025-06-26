<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    ClipboardDocumentCheckIcon,
    EyeIcon,
    CheckIcon,
    XMarkIcon,
    CalendarIcon,
    UserIcon,
    BuildingOfficeIcon,
    ClockIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline';

// Modal para ver detalles de la planificación
import DetallesPlanificacionModal from './DetallesPlanificacionModal.vue';

const props = defineProps({
    visitasPendientes: {
        type: Object,
        default: () => ({ data: [] })
    },
    auth: Object
});

const showDetallesModal = ref(false);
const planificacionSeleccionada = ref(null);
const procesando = ref({});

// Agrupar visitas por vendedor y semana
const visitasAgrupadas = computed(() => {
    const agrupadas = {};
    
    props.visitasPendientes.data.forEach(visita => {
        const vendedorId = visita.user_id;
        const vendedorName = visita.usuario?.name || 'Sin asignar';
        const semana = visita.semana;
        const año = visita.año;
        const key = `${vendedorId}-${semana}-${año}`;
        
        if (!agrupadas[key]) {
            agrupadas[key] = {
                vendedor: {
                    id: vendedorId,
                    name: vendedorName
                },
                semana,
                año,
                visitas: [],
                fechaEnvio: visita.fecha_envio_aprobacion
            };
        }
        
        agrupadas[key].visitas.push(visita);
    });
    
    return Object.values(agrupadas);
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatTime = (time) => {
    if (!time) return '';
    return new Date('2000-01-01 ' + time).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const abrirDetalles = (planificacion) => {
    planificacionSeleccionada.value = planificacion;
    showDetallesModal.value = true;
};

const cerrarDetalles = () => {
    showDetallesModal.value = false;
    planificacionSeleccionada.value = null;
};

const aprobarPlanificacion = async (planificacion, aprobar = true) => {
    const key = `${planificacion.vendedor.id}-${planificacion.semana}-${planificacion.año}`;
    
    if (!confirm(aprobar ? '¿Aprobar esta planificación?' : '¿Rechazar esta planificación?')) {
        return;
    }
    
    procesando.value[key] = true;
    
    try {
        const response = await fetch('/visitas/aprobar-planificacion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                vendedor_id: planificacion.vendedor.id,
                semana: planificacion.semana,
                año: planificacion.año,
                aprobar: aprobar,
                comentarios: ''
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            router.reload();
        } else {
            alert(data.message || 'Error al procesar la aprobación');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al procesar la aprobación');
    } finally {
        procesando.value[key] = false;
    }
};

const calcularEstadisticas = (visitas) => {
    return {
        total: visitas.length,
        clientes: [...new Set(visitas.map(v => v.cliente_id))].length,
        comerciales: visitas.filter(v => v.tipo === 'comercial').length,
        tecnicas: visitas.filter(v => v.tipo === 'tecnica').length,
        seguimientos: visitas.filter(v => v.tipo === 'seguimiento').length
    };
};

const getRangoSemana = (semana, año) => {
    const date = new Date(año, 0, 1 + (semana - 1) * 7);
    const startOfWeek = new Date(date.setDate(date.getDate() - date.getDay() + 1));
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    
    const inicio = startOfWeek.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
    const fin = endOfWeek.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
    
    return `${inicio} - ${fin}`;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header con estadísticas generales -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-100">
                    Aprobaciones Pendientes
                </h3>
                <div class="flex items-center text-gray-400">
                    <ClipboardDocumentCheckIcon class="w-5 h-5 mr-2" />
                    <span>{{ visitasAgrupadas.length }} planificaciones pendientes</span>
                </div>
            </div>
            
            <div v-if="visitasAgrupadas.length === 0" class="text-center py-8">
                <ClipboardDocumentCheckIcon class="w-16 h-16 text-gray-600 mx-auto mb-4" />
                <h4 class="text-lg font-medium text-gray-300 mb-2">No hay aprobaciones pendientes</h4>
                <p class="text-gray-400">Todas las planificaciones han sido procesadas</p>
            </div>
        </div>
        
        <!-- Lista de planificaciones -->
        <div v-if="visitasAgrupadas.length > 0" class="space-y-4">
            <div
                v-for="planificacion in visitasAgrupadas"
                :key="`${planificacion.vendedor.id}-${planificacion.semana}-${planificacion.año}`"
                class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden"
            >
                <!-- Header de la planificación -->
                <div class="p-6 border-b border-gray-800 bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-100">
                                    Planificación Semana {{ planificacion.semana }}
                                </h4>
                                <div class="flex items-center text-gray-300 mt-1">
                                    <UserIcon class="w-4 h-4 mr-2" />
                                    <span class="text-sm">{{ planificacion.vendedor.name }}</span>
                                    <span class="mx-2">•</span>
                                    <CalendarIcon class="w-4 h-4 mr-1" />
                                    <span class="text-sm">{{ getRangoSemana(planificacion.semana, planificacion.año) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button
                                @click="abrirDetalles(planificacion)"
                                class="inline-flex items-center px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors"
                            >
                                <EyeIcon class="w-4 h-4 mr-1" />
                                Ver Detalles
                            </button>
                            
                            <button
                                @click="aprobarPlanificacion(planificacion, false)"
                                :disabled="procesando[`${planificacion.vendedor.id}-${planificacion.semana}-${planificacion.año}`]"
                                class="inline-flex items-center px-3 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors disabled:opacity-50"
                            >
                                <XMarkIcon class="w-4 h-4 mr-1" />
                                Rechazar
                            </button>
                            
                            <button
                                @click="aprobarPlanificacion(planificacion, true)"
                                :disabled="procesando[`${planificacion.vendedor.id}-${planificacion.semana}-${planificacion.año}`]"
                                class="inline-flex items-center px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors disabled:opacity-50"
                            >
                                <CheckIcon class="w-4 h-4 mr-1" />
                                {{ procesando[`${planificacion.vendedor.id}-${planificacion.semana}-${planificacion.año}`] ? 'Procesando...' : 'Aprobar' }}
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen de la planificación -->
                <div class="p-6">
                    <!-- Estadísticas rápidas -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-400">{{ planificacion.visitas.length }}</div>
                            <div class="text-sm text-gray-400">Total Visitas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-400">
                                {{ calcularEstadisticas(planificacion.visitas).clientes }}
                            </div>
                            <div class="text-sm text-gray-400">Clientes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-400">
                                {{ calcularEstadisticas(planificacion.visitas).comerciales }}
                            </div>
                            <div class="text-sm text-gray-400">Comerciales</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-400">
                                {{ calcularEstadisticas(planificacion.visitas).tecnicas }}
                            </div>
                            <div class="text-sm text-gray-400">Técnicas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-400">
                                {{ calcularEstadisticas(planificacion.visitas).seguimientos }}
                            </div>
                            <div class="text-sm text-gray-400">Seguimientos</div>
                        </div>
                    </div>
                    
                    <!-- Vista previa de visitas -->
                    <div class="space-y-2">
                        <h5 class="text-sm font-medium text-gray-300 mb-3">Vista previa de visitas:</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div
                                v-for="visita in planificacion.visitas.slice(0, 6)"
                                :key="visita.id"
                                class="p-3 bg-gray-800 rounded-md border border-gray-700"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-100 truncate">
                                            {{ visita.titulo }}
                                        </div>
                                        <div class="flex items-center text-gray-400 mt-1">
                                            <BuildingOfficeIcon class="w-3 h-3 mr-1" />
                                            <span class="text-xs truncate">{{ visita.cliente.razon_social }}</span>
                                        </div>
                                        <div class="flex items-center text-gray-400 mt-1">
                                            <CalendarIcon class="w-3 h-3 mr-1" />
                                            <span class="text-xs">{{ formatDate(visita.fecha_programada) }}</span>
                                            <span v-if="visita.hora_planificada" class="ml-2 text-xs">
                                                {{ formatTime(visita.hora_planificada) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="planificacion.visitas.length > 6" class="text-center mt-3">
                            <span class="text-sm text-gray-400">
                                y {{ planificacion.visitas.length - 6 }} visitas más...
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de detalles -->
    <DetallesPlanificacionModal
        v-if="showDetallesModal && planificacionSeleccionada"
        :planificacion="planificacionSeleccionada"
        @close="cerrarDetalles"
        @aprobar="aprobarPlanificacion"
    />
</template> 