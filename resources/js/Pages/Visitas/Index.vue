<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    PlusIcon
} from '@heroicons/vue/24/outline';

// Componentes de las pestañas
import PlanificacionTab from './Components/PlanificacionTab.vue';
import EjecucionTab from './Components/EjecucionTab.vue';
import AprobacionesTab from './Components/AprobacionesTab.vue';

const props = defineProps({
    visitas: Object,
    stats: Object,
    filterOptions: Object,
    filters: Object,
    sort: Object,
    userRole: String,
    visitasPendientesAprobacion: {
        type: Object,
        default: () => ({ data: [] })
    },
    auth: Object
});

// Estado de pestañas
const activeTab = ref('planificacion');

const setActiveTab = (tabId) => {
    activeTab.value = tabId;
};
</script>

<template>
    <Head title="Gestión de Visitas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100 mb-4">
                        Gestión de Visitas
                    </h2>
                    <!-- Pestañas como en clientes -->
                    <nav class="flex space-x-8">
                        <button
                            @click="setActiveTab('planificacion')"
                            :class="[
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'planificacion'
                                    ? 'border-blue-500 text-blue-400'
                                    : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300'
                            ]"
                        >
                            Planificación
                        </button>
                        <button
                            @click="setActiveTab('ejecucion')"
                            :class="[
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'ejecucion'
                                    ? 'border-blue-500 text-blue-400'
                                    : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300'
                            ]"
                        >
                            Ejecución
                        </button>
                        <button
                            v-if="userRole === 'gerente'"
                            @click="setActiveTab('aprobaciones')"
                            :class="[
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors relative',
                                activeTab === 'aprobaciones'
                                    ? 'border-blue-500 text-blue-400'
                                    : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300'
                            ]"
                        >
                            Aprobaciones
                            <span
                                v-if="visitasPendientesAprobacion?.data?.length > 0"
                                class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white"
                            >
                                {{ visitasPendientesAprobacion.data.length }}
                            </span>
                        </button>
                    </nav>
                </div>
                <div class="flex space-x-3">
                    <Link
                        v-if="userRole === 'vendedor' && activeTab === 'ejecucion'"
                        :href="route('visitas.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md border border-blue-500 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Nueva Visita
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Contenido de las pestañas -->
                <div class="tab-content">
                    <!-- Pestaña Planificación -->
                    <div v-if="activeTab === 'planificacion'">
                        <PlanificacionTab 
                            :user-role="userRole"
                            :auth="auth"
                        />
                    </div>

                    <!-- Pestaña Ejecución -->
                    <div v-if="activeTab === 'ejecucion'">
                        <EjecucionTab 
                            :visitas="visitas"
                            :stats="stats"
                            :filter-options="filterOptions"
                            :filters="filters"
                            :sort="sort"
                            :user-role="userRole"
                            :auth="auth"
                        />
                    </div>

                    <!-- Pestaña Aprobaciones (solo gerentes) -->
                    <div v-if="activeTab === 'aprobaciones' && userRole === 'gerente'">
                        <AprobacionesTab 
                            :visitas-pendientes="visitasPendientesAprobacion"
                            :auth="auth"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style> 