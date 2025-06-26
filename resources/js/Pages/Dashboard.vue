<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    CheckCircleIcon, 
    DocumentChartBarIcon, 
    ArchiveBoxIcon, 
    BanknotesIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    metricas: Object,
    graficos: Object,
    actividades: Object,
    alertas: Object,
    visitasProximas: Object,
    userRole: String,
    auth: Object
});

const getCambioColor = (valor) => {
    if (valor > 0) return 'text-green-400';
    if (valor < 0) return 'text-red-400';
    return 'text-gray-500';
};

const metricasUI = computed(() => {
    if (props.userRole === 'vendedor') {
        return [
            {
                nombre: 'Mis Visitas del Mes',
                valor: props.metricas.mis_visitas.valor,
                cambio: props.metricas.mis_visitas.cambio,
                icono: CheckCircleIcon,
            },
            {
                nombre: 'Cumplimiento de Visitas',
                valor: `${props.metricas.cumplimiento_visitas.valor}%`,
                cambio: props.metricas.cumplimiento_visitas.cambio,
                icono: DocumentChartBarIcon,
            },
            {
                nombre: 'Clientes Visitados',
                valor: props.metricas.clientes_visitados.valor,
                cambio: props.metricas.clientes_visitados.cambio,
                icono: ArchiveBoxIcon,
            },
            {
                nombre: 'Visitas Pendientes',
                valor: props.metricas.visitas_pendientes.valor,
                cambio: props.metricas.visitas_pendientes.cambio,
                icono: BanknotesIcon,
            }
        ];
    } else if (props.userRole === 'gerente') {
        return [
            {
                nombre: 'Equipo de Vendedores',
                valor: props.metricas.equipo_vendedores.valor,
                cambio: props.metricas.equipo_vendedores.cambio,
                icono: CheckCircleIcon,
            },
            {
                nombre: 'Visitas del Equipo',
                valor: props.metricas.visitas_equipo.valor,
                cambio: props.metricas.visitas_equipo.cambio,
                icono: DocumentChartBarIcon,
            },
            {
                nombre: 'Tasa de Cumplimiento',
                valor: `${props.metricas.tasa_cumplimiento.valor}%`,
                cambio: props.metricas.tasa_cumplimiento.cambio,
                icono: ArchiveBoxIcon,
            },
            {
                nombre: 'Aprobaciones Pendientes',
                valor: props.metricas.aprobaciones_pendientes.valor,
                cambio: props.metricas.aprobaciones_pendientes.cambio,
                icono: BanknotesIcon,
            }
        ];
    } else {
        // Dashboard general (datos de demo)
        return [
            {
                nombre: 'Cumplimiento de Visitas',
                valor: `${props.metricas.cumplimiento_visitas.valor}%`,
                cambio: props.metricas.cumplimiento_visitas.cambio,
                icono: CheckCircleIcon,
            },
            {
                nombre: 'Cotizaciones del Mes',
                valor: props.metricas.cotizaciones.valor,
                cambio: props.metricas.cotizaciones.cambio,
                icono: DocumentChartBarIcon,
            },
            {
                nombre: 'Pedidos Realizados',
                valor: props.metricas.pedidos.valor,
                cambio: props.metricas.pedidos.cambio,
                icono: ArchiveBoxIcon,
            },
            {
                nombre: 'Margen Bruto',
                valor: `${props.metricas.margen.valor}%`,
                cambio: props.metricas.margen.cambio,
                icono: BanknotesIcon,
            }
        ];
    }
});

const chartData = computed(() => {
    return props.graficos?.visitas_semanales || [];
});

const maxValue = computed(() => {
    if (!chartData.value || chartData.value.length === 0) return 10; // Un valor por defecto si no hay datos
    return Math.max(...chartData.value.map(week => Math.max(week.planificadas, week.total_realizadas))) || 10;
});

const tituloGrafico = computed(() => {
    if (props.userRole === 'vendedor') {
        return 'Mis Visitas Semanales';
    } else if (props.userRole === 'gerente') {
        return 'Visitas del Equipo - Semanales';
    } else {
        return 'Desempeño de Visitas Semanales';
    }
});

const descripcionGrafico = computed(() => {
    if (props.userRole === 'vendedor') {
        return 'Mis visitas planificadas vs. realizadas.';
    } else if (props.userRole === 'gerente') {
        return 'Visitas del equipo planificadas vs. realizadas.';
    } else {
        return 'Comparativa de visitas planificadas vs. realizadas.';
    }
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard de Demo</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header del Dashboard -->
                <div class="mb-8 px-4 sm:px-0">
                    <h1 class="text-2xl font-bold text-gray-100">Dashboard</h1>
                    <p class="text-gray-400">Bienvenido, {{ auth.user.name }}. Aquí tienes un resumen de tu actividad.</p>
                </div>

                <!-- 4 Cards de Métricas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="metrica in metricasUI" :key="metrica.nombre" class="bg-gray-900 border border-gray-800 rounded-lg p-6 flex flex-col justify-between hover:bg-gray-800/50 transition-colors duration-300">
                        <div class="flex justify-between items-start">
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-gray-400">{{ metrica.nombre }}</p>
                                <p class="text-3xl font-bold text-gray-100 mt-2">{{ metrica.valor }}</p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-500/10 text-blue-500">
                                <component :is="metrica.icono" class="h-6 w-6" />
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <div class="flex items-center text-xs" :class="getCambioColor(metrica.cambio)">
                                <ArrowTrendingUpIcon v-if="metrica.cambio > 0" class="h-4 w-4 mr-1"/>
                                <ArrowTrendingDownIcon v-if="metrica.cambio < 0" class="h-4 w-4 mr-1"/>
                                <span v-if="metrica.cambio !== 0">{{ Math.abs(metrica.cambio) }}% vs mes anterior</span>
                                <span v-else>Sin cambios vs mes anterior</span>
                            </div>
                            <Link href="#" class="text-xs font-medium text-blue-500 hover:text-blue-400 flex items-center">
                                Ver más
                                <ChevronRightIcon class="h-3 w-3 ml-1" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Gráfico Estilo Monocromático -->
                <div class="mt-8 bg-slate-900/70 border border-slate-800 rounded-xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-200">{{ tituloGrafico }}</h3>
                            <p class="text-sm text-slate-400">{{ descripcionGrafico }}</p>
                        </div>
                        <!-- Leyenda -->
                        <div class="flex items-center space-x-4 text-xs text-slate-400">
                            <div class="flex items-center">
                                <div class="w-2.5 h-2.5 bg-slate-500 rounded-full mr-2"></div>
                                <span>Planificadas</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2.5 h-2.5 bg-sky-500 rounded-full mr-2"></div>
                                <span>Realizadas (P)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2.5 h-2.5 bg-teal-400 rounded-full mr-2"></div>
                                <span>Realizadas (E)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="h-72" v-if="chartData.length > 0">
                        <div class="grid grid-cols-6 gap-6 h-full">
                             <div v-for="week in chartData" :key="week.semana" class="relative flex flex-col items-center group text-center">
                                <!-- Tooltip detallado -->
                                <div class="absolute bottom-full mb-3 w-44 p-3 bg-slate-800/90 backdrop-blur-sm border border-slate-700 rounded-lg shadow-xl text-left text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10">
                                    <div class="font-bold text-slate-200 mb-2">{{ week.semana }}</div>
                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-slate-400">Planificadas:</span>
                                            <span class="font-semibold text-slate-200">{{ week.planificadas }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sky-400">Realizadas (P):</span>
                                            <span class="font-semibold text-sky-300">{{ week.realizadas_planificadas }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-teal-400">Realizadas (E):</span>
                                            <span class="font-semibold text-teal-300">{{ week.realizadas_no_planificadas }}</span>
                                        </div>
                                        <div class="border-t border-slate-700 my-1.5"></div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-400">Total:</span>
                                            <span class="font-bold text-slate-100">{{ week.total_realizadas }}</span>
                                        </div>
                                    </div>
                                    <div class="absolute left-1/2 -translate-x-1/2 bottom-[-5px] w-2.5 h-2.5 bg-slate-800 transform rotate-45 border-r border-b border-slate-700"></div>
                                </div>

                                <!-- Contenedor de las Barras -->
                                <div class="relative flex items-end space-x-2 h-64 w-full justify-center">
                                    <!-- Barra 1: Planificadas -->
                                    <div class="w-1/3 h-full flex items-end">
                                        <div 
                                            class="w-full bg-slate-700 rounded-t-md hover:bg-slate-600 transition-colors" 
                                            :style="{ height: `${(week.planificadas / maxValue) * 100}%` }">
                                        </div>
                                    </div>
                                    
                                    <!-- Barra 2: Stack de Realizadas -->
                                    <div class="w-1/3 h-full flex flex-col justify-end">
                                        <div class="relative w-full" :style="{ height: `${(week.total_realizadas / maxValue) * 100}%` }">
                                            <!-- Realizadas (Extra) -->
                                            <div 
                                                v-if="week.realizadas_no_planificadas > 0"
                                                class="absolute top-0 left-0 w-full bg-teal-500 hover:bg-teal-400 transition-colors rounded-t-md"
                                                :style="{ height: `${(week.realizadas_no_planificadas / week.total_realizadas) * 100}%` }">
                                            </div>
                                            <!-- Realizadas (Planificadas) -->
                                            <div 
                                                class="absolute bottom-0 left-0 w-full bg-sky-600 hover:bg-sky-500 transition-colors"
                                                :style="{ height: `${(week.realizadas_planificadas / week.total_realizadas) * 100}%` }"
                                                :class="{ 'rounded-t-md': week.realizadas_no_planificadas === 0 }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Etiqueta X-Axis -->
                                <div class="mt-3 text-xs text-slate-400 font-medium border-t border-slate-800 w-full pt-2">
                                    {{ week.semana }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="h-72 flex items-center justify-center">
                        <div class="text-slate-500">No hay datos disponibles</div>
                    </div>
                </div>

                <!-- Mensaje de sesión -->
                <div class="mt-8 bg-gray-900 border border-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-400">¡Has iniciado sesión correctamente!</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
