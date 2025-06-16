<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    PlusIcon,
    CurrencyDollarIcon,
    ClipboardDocumentListIcon,
    ChartBarIcon,
    UserPlusIcon,
    DocumentTextIcon,
    CalendarIcon,
    UserIcon as UserPlusOutlineIcon
} from '@heroicons/vue/24/outline';
import {
    UserIcon,
    BanknotesIcon,
    DocumentCheckIcon,
    MagnifyingGlassIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/solid';

// Datos de ejemplo para el dashboard
const stats = ref([
    {
        name: 'Ventas del Mes',
        value: '$45,678',
        change: '+8%',
        changeType: 'increase',
        icon: BanknotesIcon
    },
    {
        name: 'Leads Pendientes',
        value: '23',
        change: '-2%',
        changeType: 'decrease',
        icon: MagnifyingGlassIcon
    },
    {
        name: 'Tareas Completadas',
        value: '87%',
        change: '+5%',
        changeType: 'increase',
        icon: DocumentCheckIcon
    }
]);

const recentActivities = ref([
    {
        id: 1,
        type: 'client',
        message: 'Nuevo cliente registrado: Juan Pérez',
        time: 'Hace 2 horas'
    },
    {
        id: 2,
        type: 'sale',
        message: 'Venta completada por $2,500',
        time: 'Hace 4 horas'
    },
    {
        id: 3,
        type: 'task',
        message: 'Tarea completada: Seguimiento cliente ABC',
        time: 'Hace 6 horas'
    },
    {
        id: 4,
        type: 'lead',
        message: 'Nuevo lead: María García - Interesada en producto X',
        time: 'Hace 8 horas'
    }
]);

// Control de semanas
const currentWeek = ref(0); // 0 es la semana actual

const getCurrentWeekRange = (weekOffset = 0) => {
    const now = new Date();
    const startOfWeek = new Date(now);
    const dayOfWeek = now.getDay();
    const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1) + (weekOffset * 7);
    startOfWeek.setDate(diff);
    
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    
    return {
        start: startOfWeek,
        end: endOfWeek
    };
};

const weekRange = computed(() => {
    const range = getCurrentWeekRange(currentWeek.value);
    const options = { 
        day: 'numeric', 
        month: 'short',
        timeZone: 'America/Mexico_City'
    };
    
    return {
        start: range.start.toLocaleDateString('es-ES', options),
        end: range.end.toLocaleDateString('es-ES', options),
        year: range.start.getFullYear()
    };
});

const weekLabel = computed(() => {
    if (currentWeek.value === 0) return 'Esta semana';
    if (currentWeek.value === -1) return 'Semana pasada';
    if (currentWeek.value === 1) return 'Próxima semana';
    return currentWeek.value < 0 ? `Hace ${Math.abs(currentWeek.value)} semanas` : `En ${currentWeek.value} semanas`;
});

const changeWeek = (direction) => {
    currentWeek.value += direction;
};

// Datos del gráfico de desempeño
const currentChartWeek = ref(0);

const getChartWeekRange = (weekOffset = 0) => {
    const now = new Date();
    const startOfWeek = new Date(now);
    const dayOfWeek = now.getDay();
    const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1) + (weekOffset * 7);
    startOfWeek.setDate(diff);
    return startOfWeek;
};

const chartData = computed(() => {
    const weeks = [];
    for (let i = 5; i >= 0; i--) {
        const weekStart = getChartWeekRange(currentChartWeek.value - i);
        const weekLabel = weekStart.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        
        // Datos de ejemplo
        const planned = Math.floor(Math.random() * 20) + 10; // 10-30 visitas planificadas
        const completedPlanned = Math.floor(planned * (0.7 + Math.random() * 0.3)); // 70-100% de las planificadas se realizaron
        const completedUnplanned = Math.floor(Math.random() * 5) + 1; // 1-5 visitas no planificadas realizadas
        const totalCompleted = completedPlanned + completedUnplanned;
        
        weeks.push({
            week: weekLabel,
            planned,
            completedPlanned,
            completedUnplanned,
            totalCompleted
        });
    }
    return weeks;
});

const changeChartWeek = (direction) => {
    currentChartWeek.value += direction;
};

const maxValue = computed(() => {
    return Math.max(...chartData.value.map(week => Math.max(week.planned, week.totalCompleted)));
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-100">
                Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Estadísticas principales con selector de semanas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Selector de semanas (reemplaza Total Clientes) -->
                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 h-32">
                        <div class="text-center h-full flex flex-col justify-between">
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <button
                                    @click="changeWeek(-1)"
                                    class="p-1 text-gray-400 hover:text-gray-200 hover:bg-gray-800 rounded transition-colors"
                                >
                                    <ChevronLeftIcon class="w-4 h-4" />
                                </button>
                                
                                <h3 class="text-sm font-semibold text-gray-100">{{ weekLabel }}</h3>
                                
                                <button
                                    @click="changeWeek(1)"
                                    class="p-1 text-gray-400 hover:text-gray-200 hover:bg-gray-800 rounded transition-colors"
                                >
                                    <ChevronRightIcon class="w-4 h-4" />
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mb-2">
                                {{ weekRange.start }} - {{ weekRange.end }}
                            </p>
                            <div class="h-6 flex items-center justify-center">
                                <button
                                    @click="currentWeek = 0"
                                    class="px-2 py-1 text-xs font-medium text-blue-400 bg-blue-600/10 border border-blue-500/30 rounded hover:bg-blue-600/20 transition-colors"
                                    v-if="currentWeek !== 0"
                                >
                                    Hoy
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Otras estadísticas -->
                    <div
                        v-for="stat in stats"
                        :key="stat.name"
                        class="bg-gray-900 border border-gray-800 rounded-lg p-6 hover:bg-gray-800/50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-400">{{ stat.name }}</p>
                                <p class="text-2xl font-bold text-gray-100">{{ stat.value }}</p>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <component :is="stat.icon" class="w-8 h-8 text-blue-400" />
                                <span
                                    :class="[
                                        'text-sm font-medium',
                                        stat.changeType === 'increase' ? 'text-blue-400' : 'text-red-400'
                                    ]"
                                >
                                    {{ stat.change }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Desempeño y Acciones Rápidas -->
                <div class="mb-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Gráfico de Desempeño -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 h-[500px] flex flex-col">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-100">Desempeño de Visitas</h3>
                                    <p class="text-sm text-gray-400">Visitas realizadas vs planificadas</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="changeChartWeek(-1)"
                                        class="p-2 text-gray-400 hover:text-gray-200 hover:bg-gray-800 rounded-md transition-colors"
                                    >
                                        <ChevronLeftIcon class="w-4 h-4" />
                                    </button>
                                    <span class="text-sm text-gray-400 px-3">Últimas 6 semanas</span>
                                    <button
                                        @click="changeChartWeek(1)"
                                        class="p-2 text-gray-400 hover:text-gray-200 hover:bg-gray-800 rounded-md transition-colors"
                                    >
                                        <ChevronRightIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Gráfico de barras -->
                            <div class="flex-1 flex">
                                <!-- Eje Y -->
                                <div class="flex flex-col justify-between text-xs text-gray-400 pr-4 h-64">
                                    <span class="flex items-center">{{ maxValue }}</span>
                                    <span class="flex items-center">{{ Math.floor(maxValue * 0.75) }}</span>
                                    <span class="flex items-center">{{ Math.floor(maxValue * 0.5) }}</span>
                                    <span class="flex items-center">{{ Math.floor(maxValue * 0.25) }}</span>
                                    <span class="flex items-center">0</span>
                                </div>
                                
                                <!-- Líneas de cuadrícula -->
                                <div class="ml-12 relative h-64 flex-1">
                                    <div class="absolute inset-0 flex flex-col justify-between">
                                        <div class="h-px bg-gray-700"></div>
                                        <div class="h-px bg-gray-700"></div>
                                        <div class="h-px bg-gray-700"></div>
                                        <div class="h-px bg-gray-700"></div>
                                        <div class="h-px bg-gray-700"></div>
                                    </div>
                                    
                                    <!-- Barras -->
                                    <div class="flex items-end justify-between h-full pt-2 pb-2">
                                        <div 
                                            v-for="week in chartData" 
                                            :key="week.week"
                                            class="flex flex-col items-center flex-1 mx-1"
                                        >
                                            <div class="flex items-end space-x-1 mb-2 h-56">
                                                <!-- Barra planificadas -->
                                                <div 
                                                    class="bg-gray-600/50 w-12 rounded-t-sm relative group cursor-pointer transition-colors hover:bg-gray-600/70"
                                                    :style="{ height: `${(week.planned / maxValue) * 100}%` }"
                                                >
                                                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                        Planificadas: {{ week.planned }}
                                                    </div>
                                                </div>
                                                
                                                <!-- Barra realizadas (apilada) -->
                                                <div class="relative w-12 flex flex-col justify-end" :style="{ height: `${(week.totalCompleted / maxValue) * 100}%` }">
                                                    <!-- Parte superior: Realizadas no planificadas -->
                                                    <div 
                                                        class="bg-amber-600 relative group cursor-pointer transition-colors hover:bg-amber-500 rounded-t-sm"
                                                        :style="{ height: `${(week.completedUnplanned / week.totalCompleted) * 100}%` }"
                                                    >
                                                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                            Realizadas no planificadas: {{ week.completedUnplanned }}
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Parte inferior: Realizadas planificadas -->
                                                    <div 
                                                        class="bg-blue-500 relative group cursor-pointer transition-colors hover:bg-blue-400 rounded-b-sm"
                                                        :style="{ height: `${(week.completedPlanned / week.totalCompleted) * 100}%` }"
                                                    >
                                                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                            Realizadas planificadas: {{ week.completedPlanned }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Etiqueta de semana -->
                                            <span class="text-xs text-gray-400 text-center">{{ week.week }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Leyenda -->
                            <div class="flex justify-center space-x-6 mt-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-gray-600/50 rounded mr-2"></div>
                                    <span class="text-sm text-gray-400">Planificadas</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                                    <span class="text-sm text-gray-400">Realizadas planificadas</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-amber-600 rounded mr-2"></div>
                                    <span class="text-sm text-gray-400">Realizadas no planificadas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones rápidas y Próximas tareas -->
                    <div class="flex flex-col space-y-6 h-[500px]">
                        <!-- Acciones rápidas -->
                        <div class="bg-gray-900 border border-gray-800 rounded-lg flex-1 flex flex-col">
                            <div class="p-6 border-b border-gray-800">
                                <h3 class="text-lg font-medium text-gray-100">Acciones Rápidas</h3>
                                <p class="text-sm text-gray-400 mt-1">Operaciones frecuentes</p>
                            </div>
                            <div class="p-6 flex-1 flex flex-col justify-center">
                                <div class="space-y-4">
                                    <button 
                                        @click="router.visit('/clientes/create')"
                                        class="w-full flex items-center justify-center px-4 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-medium rounded-lg border border-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                                    >
                                        <UserPlusOutlineIcon class="w-5 h-5 mr-3" />
                                        <span class="font-semibold">Nuevo Cliente</span>
                                    </button>
                                    
                                    <button 
                                        @click="router.visit('/contactos/create')"
                                        class="w-full flex items-center justify-center px-4 py-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm font-medium rounded-lg border border-green-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                                    >
                                        <UserPlusIcon class="w-5 h-5 mr-3" />
                                        <span class="font-semibold">Añadir Contacto</span>
                                    </button>
                                    
                                    <button 
                                        @click="router.visit('/visitas/create')"
                                        class="w-full flex items-center justify-center px-4 py-4 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white text-sm font-medium rounded-lg border border-purple-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                                    >
                                        <CalendarIcon class="w-5 h-5 mr-3" />
                                        <span class="font-semibold">Registrar Visita</span>
                                    </button>
                                    
                                    <button 
                                        @click="alert('Funcionalidad en desarrollo')"
                                        disabled
                                        class="w-full flex items-center justify-center px-4 py-4 bg-gray-800 text-gray-400 text-sm font-medium rounded-lg border border-gray-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed relative"
                                    >
                                        <DocumentTextIcon class="w-5 h-5 mr-3" />
                                        <span class="font-semibold">Generar Cotización</span>
                                        <span class="absolute -top-1 -right-1 px-2 py-1 text-xs bg-amber-600 text-white rounded-full">Pronto</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Próximas tareas -->
                        <div class="bg-gray-900 border border-gray-800 rounded-lg flex-1 flex flex-col">
                            <div class="p-6 border-b border-gray-800">
                                <h3 class="text-lg font-medium text-gray-100">Próximas Tareas</h3>
                                <p class="text-sm text-gray-400 mt-1">Pendientes de hoy</p>
                            </div>
                            <div class="p-6 flex-1">
                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3 p-3 bg-gray-800/30 rounded-md hover:bg-gray-800/50 transition-colors">
                                        <input type="checkbox" class="h-4 w-4 mt-1 text-blue-600 bg-gray-800 border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-100">Llamar a cliente ABC Corp</span>
                                            <p class="text-xs text-gray-400 mt-1">Seguimiento de propuesta - Vence en 2 horas</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3 p-3 bg-gray-800/30 rounded-md hover:bg-gray-800/50 transition-colors">
                                        <input type="checkbox" class="h-4 w-4 mt-1 text-blue-600 bg-gray-800 border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-100">Enviar propuesta XYZ</span>
                                            <p class="text-xs text-gray-400 mt-1">Revisión final - Vence mañana</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3 p-3 bg-gray-800/30 rounded-md hover:bg-gray-800/50 transition-colors">
                                        <input type="checkbox" checked class="h-4 w-4 mt-1 text-blue-600 bg-gray-800 border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                                        <div class="flex-1">
                                            <span class="text-sm text-gray-400 line-through">Actualizar base de datos</span>
                                            <p class="text-xs text-gray-500 mt-1">Completado hace 1 hora</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid principal -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Actividad reciente -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-900 border border-gray-800 rounded-lg">
                            <div class="p-6 border-b border-gray-800">
                                <h3 class="text-lg font-medium text-gray-100">Actividad Reciente</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div
                                        v-for="activity in recentActivities"
                                        :key="activity.id"
                                        class="flex items-center space-x-4 p-4 bg-gray-800/50 rounded-md border border-gray-800"
                                    >
                                        <div
                                            :class="[
                                                'w-2 h-2 rounded-full',
                                                activity.type === 'client' ? 'bg-blue-500' :
                                                activity.type === 'sale' ? 'bg-green-500' :
                                                activity.type === 'task' ? 'bg-yellow-500' : 'bg-purple-500'
                                            ]"
                                        ></div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-100">{{ activity.message }}</p>
                                            <p class="text-xs text-gray-400">{{ activity.time }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Área vacía para mantener proporción -->
                    <div></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
