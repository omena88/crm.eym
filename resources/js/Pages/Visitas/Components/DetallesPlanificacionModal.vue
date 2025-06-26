<script setup>
import { ref, computed } from 'vue';
import {
    XMarkIcon,
    CheckIcon,
    XMarkIcon as RejectIcon,
    CalendarIcon,
    ClockIcon,
    BuildingOfficeIcon,
    UserIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    planificacion: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'aprobar']);

const comentarios = ref('');
const procesando = ref(false);

const visitasPorDia = computed(() => {
    const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    const visitasAgrupadas = {};
    
    // Inicializar todos los días
    diasSemana.forEach((dia, index) => {
        visitasAgrupadas[index + 1] = {
            nombre: dia,
            visitas: []
        };
    });
    
    // Agrupar visitas por día de la semana
    props.planificacion.visitas.forEach(visita => {
        const fecha = new Date(visita.fecha_programada);
        const diaSemana = fecha.getDay() === 0 ? 7 : fecha.getDay(); // Convertir domingo de 0 a 7
        visitasAgrupadas[diaSemana].visitas.push(visita);
    });
    
    return Object.values(visitasAgrupadas);
});

const estadisticas = computed(() => {
    const visitas = props.planificacion.visitas;
    return {
        total: visitas.length,
        clientes: [...new Set(visitas.map(v => v.cliente_id))].length,
        porTipo: {
            comercial: visitas.filter(v => v.tipo === 'comercial').length,
            tecnica: visitas.filter(v => v.tipo === 'tecnica').length,
            seguimiento: visitas.filter(v => v.tipo === 'seguimiento').length,
            postventa: visitas.filter(v => v.tipo === 'postventa').length
        },
        duracionTotal: visitas.reduce((total, v) => total + (v.duracion_estimada || 60), 0)
    };
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short'
    });
};

const formatTime = (time) => {
    if (!time) return '';
    return new Date('2000-01-01 ' + time).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const aprobar = async (aprobarPlanificacion) => {
    procesando.value = true;
    try {
        await emit('aprobar', props.planificacion, aprobarPlanificacion);
    } finally {
        procesando.value = false;
    }
};

const close = () => {
    emit('close');
};

const getTipoColor = (tipo) => {
    const colores = {
        comercial: 'bg-green-100 text-green-800',
        tecnica: 'bg-blue-100 text-blue-800',
        seguimiento: 'bg-purple-100 text-purple-800',
        postventa: 'bg-indigo-100 text-indigo-800'
    };
    return colores[tipo] || 'bg-gray-100 text-gray-800';
};

const formatDuracion = (minutos) => {
    if (!minutos) return '';
    const horas = Math.floor(minutos / 60);
    const mins = minutos % 60;
    return horas > 0 ? `${horas}h ${mins}m` : `${mins}m`;
};
</script>

<template>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click="close">
        <!-- Modal -->
        <div class="bg-gray-900 rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-y-auto border border-gray-700" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-700 sticky top-0 bg-gray-900 z-10">
                <div>
                    <h3 class="text-xl font-semibold text-gray-100">
                        Detalles de Planificación - Semana {{ planificacion.semana }}
                    </h3>
                    <div class="flex items-center text-gray-300 mt-1">
                        <UserIcon class="w-4 h-4 mr-2" />
                        <span class="text-sm">{{ planificacion.vendedor.name }}</span>
                        <span class="mx-2">•</span>
                        <span class="text-sm">{{ planificacion.año }}</span>
                    </div>
                </div>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-200 transition-colors"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>
            
            <!-- Estadísticas generales -->
            <div class="p-6 border-b border-gray-700 bg-gray-800">
                <h4 class="text-lg font-medium text-gray-100 mb-4">Resumen de la Planificación</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-400">{{ estadisticas.total }}</div>
                        <div class="text-sm text-gray-400">Total Visitas</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-400">{{ estadisticas.clientes }}</div>
                        <div class="text-sm text-gray-400">Clientes</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-400">{{ estadisticas.porTipo.comercial }}</div>
                        <div class="text-sm text-gray-400">Comerciales</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-400">{{ estadisticas.porTipo.tecnica }}</div>
                        <div class="text-sm text-gray-400">Técnicas</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-400">{{ estadisticas.porTipo.seguimiento }}</div>
                        <div class="text-sm text-gray-400">Seguimientos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-300">{{ formatDuracion(estadisticas.duracionTotal) }}</div>
                        <div class="text-sm text-gray-400">Duración Total</div>
                    </div>
                </div>
            </div>
            
            <!-- Planificación por días -->
            <div class="p-6">
                <h4 class="text-lg font-medium text-gray-100 mb-4">Distribución Semanal</h4>
                <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
                    <div
                        v-for="dia in visitasPorDia"
                        :key="dia.nombre"
                        class="bg-gray-800 rounded-lg p-4 border border-gray-700"
                    >
                        <h5 class="font-medium text-gray-200 mb-3 text-center">
                            {{ dia.nombre }}
                            <span class="text-sm text-gray-400 ml-1">({{ dia.visitas.length }})</span>
                        </h5>
                        
                        <div class="space-y-2">
                            <div
                                v-for="visita in dia.visitas"
                                :key="visita.id"
                                class="p-3 bg-gray-700 rounded-md border border-gray-600 hover:bg-gray-600 transition-colors"
                            >
                                <div class="space-y-2">
                                    <div class="font-medium text-gray-100 text-sm">
                                        {{ visita.titulo }}
                                    </div>
                                    
                                    <div class="flex items-center text-gray-300">
                                        <BuildingOfficeIcon class="w-3 h-3 mr-1 flex-shrink-0" />
                                        <span class="text-xs truncate">{{ visita.cliente.razon_social }}</span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center text-gray-400">
                                            <ClockIcon class="w-3 h-3 mr-1" />
                                            <span class="text-xs">
                                                {{ visita.hora_planificada ? formatTime(visita.hora_planificada) : 'Sin hora' }}
                                            </span>
                                        </div>
                                        
                                        <span 
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                            :class="getTipoColor(visita.tipo)"
                                        >
                                            {{ visita.tipo.charAt(0).toUpperCase() + visita.tipo.slice(1) }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="visita.descripcion" class="text-xs text-gray-400 line-clamp-2">
                                        {{ visita.descripcion }}
                                    </div>
                                    
                                    <div v-if="visita.duracion_estimada" class="text-xs text-gray-400">
                                        Duración: {{ formatDuracion(visita.duracion_estimada) }}
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="dia.visitas.length === 0" class="text-center text-gray-500 text-sm py-4">
                                Sin visitas
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista detallada de visitas -->
            <div class="p-6 border-t border-gray-700">
                <h4 class="text-lg font-medium text-gray-100 mb-4">Lista Completa de Visitas</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800">
                        <thead class="bg-gray-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Título</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Duración</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-900 divide-y divide-gray-800">
                            <tr v-for="visita in planificacion.visitas" :key="visita.id" class="hover:bg-gray-800">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-100">{{ visita.titulo }}</div>
                                    <div v-if="visita.descripcion" class="text-xs text-gray-400 truncate max-w-xs">
                                        {{ visita.descripcion }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-100">{{ visita.cliente.razon_social }}</td>
                                <td class="px-4 py-3 text-sm text-gray-100">{{ formatDate(visita.fecha_programada) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-100">
                                    {{ visita.hora_planificada ? formatTime(visita.hora_planificada) : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                        :class="getTipoColor(visita.tipo)"
                                    >
                                        {{ visita.tipo.charAt(0).toUpperCase() + visita.tipo.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-100">
                                    {{ formatDuracion(visita.duracion_estimada) || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Comentarios del gerente -->
            <div class="p-6 border-t border-gray-700">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Comentarios (opcionales)
                </label>
                <textarea
                    v-model="comentarios"
                    rows="3"
                    placeholder="Agrega comentarios sobre esta planificación..."
                    class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                ></textarea>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex justify-end space-x-3 p-6 border-t border-gray-700 bg-gray-800">
                <button
                    @click="close"
                    class="px-4 py-2 text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-md transition-colors"
                >
                    Cerrar
                </button>
                
                <button
                    @click="aprobar(false)"
                    :disabled="procesando"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors disabled:opacity-50"
                >
                    <RejectIcon class="w-4 h-4 mr-2" />
                    {{ procesando ? 'Procesando...' : 'Rechazar' }}
                </button>
                
                <button
                    @click="aprobar(true)"
                    :disabled="procesando"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors disabled:opacity-50"
                >
                    <CheckIcon class="w-4 h-4 mr-2" />
                    {{ procesando ? 'Procesando...' : 'Aprobar Planificación' }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style> 