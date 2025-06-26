<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { ArrowLeftIcon, CheckCircleIcon, Cog6ToothIcon, ClockIcon, ExclamationTriangleIcon, ChatBubbleBottomCenterTextIcon, DocumentArrowDownIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';
import ConfirmarEnvioAdministrativoModal from './Components/ConfirmarEnvioAdministrativoModal.vue';

const props = defineProps({
    pedido: Object,
    userRole: String,
});

const mostrarModalEnvio = ref(false);
const estadoFlujo = ref(props.pedido.estado);
const isSimulating = ref(false);

watch(() => props.pedido.estado, (newEstado) => {
    estadoFlujo.value = newEstado;
});

const flujo = [
    { id: 'borrador', nombre: 'Borrador', roles: ['vendedor'] },
    { id: 'revision', nombre: 'En Revisión', roles: ['vendedor'] },
    { id: 'aprobado', nombre: 'Aprobado', roles: ['vendedor'] },
    { id: 'enviado', nombre: 'Enviado a Administración', roles: ['vendedor'] },
];

const estadoActual = computed(() => flujo.find(e => e.id === estadoFlujo.value));

const cambiarEstado = (nuevoEstado, simulacion = false) => {
    if (isSimulating.value) return;

    const formEstado = useForm({
        pedido_id: parseInt(props.pedido.id),
        nuevo_estado: nuevoEstado,
    });
    
    if (simulacion) {
        isSimulating.value = true;
        setTimeout(() => {
            formEstado.post(route('pedidos.simularAccion'), {
                onSuccess: (page) => {
                    isSimulating.value = false;
                },
                onError: (errors) => {
                    console.error('Error en simulación:', errors);
                    isSimulating.value = false;
                }
            });
        }, 2000);
    } else {
        formEstado.post(route('pedidos.simularAccion'), {
            onSuccess: (page) => {
                // Estado cambiado
            },
            onError: (errors) => {
                console.error('Error en cambio de estado:', errors);
            }
        });
    }
};

const puedeActuar = computed(() => {
    if (!estadoActual.value || !estadoActual.value.roles) return false;
    
    if (estadoFlujo.value === 'borrador' && props.userRole === 'vendedor') return true;
    if (estadoFlujo.value === 'revision' && props.userRole === 'vendedor') return true;
    if (estadoFlujo.value === 'aprobado' && props.userRole === 'vendedor') return true;
    
    return estadoActual.value.roles.includes(props.userRole);
});

const enviarParaRevision = () => cambiarEstado('revision');
const aprobarPedido = () => cambiarEstado('aprobado', true);
const enviarAAdministracion = () => {
    cambiarEstado('enviado');
    mostrarModalEnvio.value = false;
};

const getStatusClass = (estadoId) => {
    const currentIndex = flujo.findIndex(e => e.id === estadoFlujo.value);
    const itemIndex = flujo.findIndex(e => e.id === estadoId);
    if (itemIndex < currentIndex) {
        return 'text-green-400 border-green-500';
    }
    if (estadoId === estadoFlujo.value) {
        return 'text-blue-400 border-blue-500';
    }
    return 'text-gray-500 border-gray-700';
};

const getIcon = (estadoId) => {
    const commonClass = "w-6 h-6";
    const currentIndex = flujo.findIndex(e => e.id === estadoFlujo.value);
    const itemIndex = flujo.findIndex(e => e.id === estadoId);
    
    if (itemIndex < currentIndex) {
        return { component: CheckCircleIcon, class: `text-green-400 ${commonClass}` };
    }
    if (estadoId === estadoFlujo.value) {
        if(estadoId.includes('revision')) return { component: Cog6ToothIcon, class: `text-blue-400 animate-spin-slow ${commonClass}` };
        return { component: ClockIcon, class: `text-blue-400 ${commonClass}` };
    }
    return { component: ClockIcon, class: `text-gray-500 ${commonClass}` };
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP'
    }).format(value);
};
</script>

<template>
    <Head :title="`Pedido ${pedido.numero}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('pedidos.index')" class="text-gray-400 hover:text-white">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-100">
                        {{ pedido.numero }} (Demo)
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Gestionando pedido para <span class="font-medium text-white">{{ pedido.cliente?.razon_social || 'Cliente no disponible' }}</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Timeline de Flujo de Aprobación -->
                <div class="mb-8 overflow-x-auto pb-4">
                    <div class="relative">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-0.5 bg-gray-700"></div>
                        <div class="relative flex justify-between">
                            <div v-for="(estado) in flujo" :key="estado.id" class="flex flex-col items-center text-center w-32">
                                 <div class="relative z-10 flex items-center justify-center w-12 h-12 rounded-full border-2 bg-gray-900" :class="getStatusClass(estado.id)">
                                    <component :is="getIcon(estado.id).component" :class="getIcon(estado.id).class" />
                                </div>
                                <p class="mt-2 text-xs" :class="getStatusClass(estado.id).replace('border-', 'text-')">{{ estado.nombre }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Columna de Acciones -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 sticky top-8">
                            <h3 class="text-lg font-semibold text-white mb-4">Acciones y Estado</h3>
                            
                            <div class="text-center p-4 rounded-lg bg-gray-800/50 border border-gray-700 mb-4">
                               <p class="text-sm text-gray-400">Estado Actual</p>
                               <p class="text-xl font-bold" :class="getStatusClass(estadoFlujo).replace('border-', 'text-')">
                                   {{ estadoActual?.nombre }}
                               </p>
                            </div>

                             <div v-if="!puedeActuar && pedido.estado !== 'enviado'" class="text-center p-3 rounded-lg bg-yellow-900/50 border border-yellow-800 text-yellow-300 text-sm">
                                <ExclamationTriangleIcon class="w-5 h-5 inline-block mr-2" />
                                No tienes acciones pendientes.
                            </div>

                            <div v-if="puedeActuar">
                                <!-- Estado: Borrador -->
                                <button v-if="estadoFlujo === 'borrador'" 
                                        @click="enviarParaRevision" 
                                        class="w-full btn-primary"
                                        :disabled="isSimulating">
                                    Enviar para Revisión
                                </button>
                                
                                <!-- Estado: Revision -->
                                <button v-if="estadoFlujo === 'revision'" 
                                        @click="aprobarPedido" 
                                        class="w-full btn-success flex items-center justify-center" 
                                        :disabled="isSimulating">
                                    <span v-if="isSimulating" class="loader mr-2"></span>
                                    <CheckCircleIcon v-else class="w-5 h-5 mr-2"/>
                                    {{ isSimulating ? 'Aprobando...' : 'Aprobar Pedido' }}
                                </button>
                                
                                <!-- Estado: Aprobado -->
                                <button v-if="estadoFlujo === 'aprobado'" 
                                        @click="mostrarModalEnvio = true" 
                                        class="w-full btn-success flex items-center justify-center"
                                        :disabled="isSimulating">
                                    <EnvelopeIcon class="w-5 h-5 mr-2"/>
                                    Enviar a Administración
                                </button>
                            </div>
                            
                             <div v-if="pedido.estado === 'enviado'" class="text-center p-3 rounded-lg bg-green-900/50 border border-green-800 text-green-300 text-sm">
                                <CheckCircleIcon class="w-5 h-5 inline-block mr-2" />
                                El pedido ha sido enviado al área administrativa.
                            </div>
                            
                             <div v-if="pedido.observaciones" class="mt-4 p-3 rounded-lg bg-yellow-900/50 border border-yellow-800">
                                <p class="text-sm font-bold text-yellow-300 flex items-center">
                                    <ChatBubbleBottomCenterTextIcon class="w-5 h-5 mr-2" />
                                    Observaciones:
                                </p>
                                <p class="text-sm text-yellow-400 mt-1">{{ pedido.observaciones }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna de Detalles -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Detalle del Pedido</h3>
                            
                            <!-- Información del pedido -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-800/50 rounded-lg">
                                <div>
                                    <p class="text-sm text-gray-400">Número de Pedido</p>
                                    <p class="text-white font-medium">{{ pedido.numero }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Fecha de Creación</p>
                                    <p class="text-white font-medium">{{ pedido.fecha_creacion }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Cliente</p>
                                    <p class="text-white font-medium">{{ pedido.cliente.razon_social }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Vendedor</p>
                                    <p class="text-white font-medium">{{ pedido.vendedor.name }}</p>
                                </div>
                            </div>
                            
                            <!-- Productos -->
                            <h4 class="text-md font-semibold text-white mb-4">Productos</h4>
                            <div class="space-y-4">
                                <div v-for="producto in pedido.productos" :key="producto.id" class="p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold text-white">{{ producto.nombre }}</p>
                                            <p class="text-sm text-gray-400">{{ producto.descripcion }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0 ml-4">
                                            <p class="text-lg font-semibold text-white">{{ formatCurrency(producto.precio_base * producto.cantidad) }}</p>
                                            <p class="text-sm text-gray-400">{{ producto.cantidad }} x {{ formatCurrency(producto.precio_base) }}</p>
                                        </div>
                                    </div>
                                     <div v-if="producto.documentos && producto.documentos.length > 0" class="mt-4 pt-4 border-t border-gray-700">
                                        <h5 class="text-xs font-bold text-gray-400 uppercase mb-2">Documentos Adjuntos</h5>
                                        <ul class="space-y-1">
                                            <li v-for="doc in producto.documentos" :key="doc.id">
                                                <a :href="`/storage/${doc.ruta}`" target="_blank" class="text-blue-400 hover:underline text-sm flex items-center space-x-2">
                                                    <DocumentArrowDownIcon class="w-4 h-4" />
                                                    <span>{{ doc.nombre }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 pt-4 border-t border-gray-700 flex justify-end items-center">
                                <span class="text-lg font-bold text-white">Total: {{ formatCurrency(pedido.total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <ConfirmarEnvioAdministrativoModal
            :show="mostrarModalEnvio"
            :pedido="pedido"
            @close="mostrarModalEnvio = false"
            @confirm="enviarAAdministracion"
        />

    </AuthenticatedLayout>
</template>

<style scoped>
.btn-primary {
    @apply w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors disabled:opacity-50;
}
.btn-success {
    @apply w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors disabled:opacity-50;
}
.animate-spin-slow {
    animation: spin 3s linear infinite;
}
.loader {
    @apply w-4 h-4 border-2 border-t-transparent border-white rounded-full animate-spin;
}
</style> 