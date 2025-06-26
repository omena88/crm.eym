<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { ArrowLeftIcon, CheckCircleIcon, XCircleIcon, PaperAirplaneIcon, Cog6ToothIcon, ClockIcon, ExclamationTriangleIcon, ChatBubbleBottomCenterTextIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';
import ConfirmarEnvioModal from './Components/ConfirmarEnvioModal.vue';

const props = defineProps({
    visita: Object,
    cotizacion: Object,
    userRole: String,
});

const mostrarModalEnvio = ref(false);

const estadoFlujo = ref(props.cotizacion.estado);
const isSimulating = ref(false);

watch(() => props.cotizacion.estado, (newEstado) => {
    estadoFlujo.value = newEstado;
});

const flujo = [
    { id: 'Borrador', nombre: 'Borrador', roles: ['vendedor'] },
    { id: 'RevisionPrecios', nombre: 'Revisión de Precios', roles: ['gerente'] },
    { id: 'PreciosAprobados', nombre: 'Precios Aprobados', roles: ['vendedor'] },
    { id: 'ListoEnviar', nombre: 'Listo para Enviar', roles: ['vendedor'] },
    { id: 'Enviada', nombre: 'Enviada a Cliente', roles: ['vendedor'] },
];

const estadoActual = computed(() => flujo.find(e => e.id === estadoFlujo.value));

const cambiarEstado = (nuevoEstado, simulacion = false) => {
    if (isSimulating.value) return;

    console.log('Cambiando estado a:', nuevoEstado, 'Simulación:', simulacion);
    console.log('Props visita completa:', props.visita);
    console.log('Visita ID:', props.visita?.id);

    // Usar el ID de la visita desde la URL si no está en props
    const visitaId = props.visita?.id || window.location.pathname.split('/').pop();
    console.log('Visita ID final:', visitaId);

    const formEstado = useForm({
        visita_id: parseInt(visitaId),
        nuevo_estado: nuevoEstado,
    });
    
    console.log('Datos del formulario:', formEstado.data());
    
    if (simulacion) {
        isSimulating.value = true;
        console.log('Iniciando simulación con delay de 2 segundos...');
        setTimeout(() => {
            console.log('Ejecutando POST después del delay...');
            formEstado.post(route('cotizaciones.simularAccion'), {
                onSuccess: (page) => {
                    console.log('POST exitoso, estado cambiado');
                    isSimulating.value = false;
                },
                onError: (errors) => {
                    console.error('Error en simulación:', errors);
                    isSimulating.value = false;
                }
            });
        }, 2000);
    } else {
        console.log('Ejecutando POST inmediato...');
        formEstado.post(route('cotizaciones.simularAccion'), {
            onSuccess: (page) => {
                console.log('POST exitoso, estado cambiado');
            },
            onError: (errors) => {
                console.error('Error en cambio de estado:', errors);
            }
        });
    }
};

const puedeActuar = computed(() => {
    if (!estadoActual.value || !estadoActual.value.roles) return false;
    
    // En modo demo, permitir acciones según el estado actual
    if (estadoFlujo.value === 'Borrador' && props.userRole === 'vendedor') return true;
    if (estadoFlujo.value === 'RevisionPrecios') return true; // Tanto vendedor como gerente pueden actuar
    if (estadoFlujo.value === 'PreciosAprobados' && props.userRole === 'vendedor') return true;
    if (estadoFlujo.value === 'ListoEnviar' && props.userRole === 'vendedor') return true;
    
    return estadoActual.value.roles.includes(props.userRole);
});

const enviarParaAprobacionPrecios = () => {
    console.log('Enviando para aprobación de precios');
    cambiarEstado('RevisionPrecios');
};

const aprobarPrecios = () => {
    console.log('Aprobando precios');
    cambiarEstado('PreciosAprobados', true);
};

const marcarComoListo = () => {
    console.log('Marcando como listo');
    cambiarEstado('ListoEnviar');
};

const confirmarEnvioCliente = () => {
    console.log('Confirmando envío a cliente');
    cambiarEstado('Enviada');
    mostrarModalEnvio.value = false;
};

const getStatusClass = (estadoId) => {
    const currentIndex = flujo.findIndex(e => e.id === estadoFlujo.value);
    const itemIndex = flujo.findIndex(e => e.id === estadoId);
    if (itemIndex < currentIndex) {
        return 'text-green-400 border-green-500';
    }
    if (estadoId === estadoFlujo.value) {
        if (estadoId.includes('Rechaza')) return 'text-red-400 border-red-500';
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
        if(estadoId.includes('Rechaza')) return { component: XCircleIcon, class: `text-red-400 ${commonClass}` };
        if(estadoId.includes('Revision')) return { component: Cog6ToothIcon, class: `text-blue-400 animate-spin-slow ${commonClass}` };
        return { component: ClockIcon, class: `text-blue-400 ${commonClass}` };
    }
    return { component: ClockIcon, class: `text-gray-500 ${commonClass}` };
}
</script>

<template>
    <Head :title="`Cotización para Visita #${visita.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('cotizaciones.index')" class="text-gray-400 hover:text-white">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-100">
                        Cotización (Demo)
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Gestionando cotización para la visita a <span class="font-medium text-white">{{ visita.cliente?.razon_social || 'Cliente no disponible' }}</span>
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
                            <div v-for="(estado) in flujo" :key="estado.id" class="flex flex-col items-center text-center w-28">
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

                             <div v-if="!puedeActuar && cotizacion.estado !== 'Enviada'" class="text-center p-3 rounded-lg bg-yellow-900/50 border border-yellow-800 text-yellow-300 text-sm">
                                <ExclamationTriangleIcon class="w-5 h-5 inline-block mr-2" />
                                No tienes acciones pendientes. Esperando a otro rol.
                            </div>

                            <div v-if="puedeActuar">
                                <!-- Acciones según el estado actual -->
                                
                                <!-- Estado: Borrador -->
                                <button v-if="estadoFlujo === 'Borrador'" 
                                        @click="enviarParaAprobacionPrecios" 
                                        class="w-full btn-primary"
                                        :disabled="isSimulating">
                                    Enviar a Aprobación de Precios
                                </button>
                                
                                <!-- Estado: RevisionPrecios (Demo - solo aprobar) -->
                                <button v-if="estadoFlujo === 'RevisionPrecios'" 
                                        @click="aprobarPrecios" 
                                        class="w-full btn-success flex items-center justify-center" 
                                        :disabled="isSimulating">
                                    <span v-if="isSimulating" class="loader mr-2"></span>
                                    <CheckCircleIcon v-else class="w-5 h-5 mr-2"/>
                                    {{ isSimulating ? 'Aprobando...' : 'Aprobar Precios' }}
                                </button>
                                
                                <!-- Estado: PreciosAprobados -->
                                <button v-if="estadoFlujo === 'PreciosAprobados'" 
                                        @click="marcarComoListo" 
                                        class="w-full btn-primary"
                                        :disabled="isSimulating">
                                    Marcar como Listo para Enviar
                                </button>
                                

                                
                                <!-- Estado: ListoEnviar -->
                                <button v-if="estadoFlujo === 'ListoEnviar'" 
                                        @click="mostrarModalEnvio = true" 
                                        class="w-full btn-success flex items-center justify-center"
                                        :disabled="isSimulating">
                                    <PaperAirplaneIcon class="w-5 h-5 mr-2"/>
                                    Enviar a Cliente
                                </button>
                            </div>
                             <div v-if="cotizacion.estado === 'Enviada'" class="space-y-3">
                                <div class="text-center p-3 rounded-lg bg-green-900/50 border border-green-800 text-green-300 text-sm">
                                    <CheckCircleIcon class="w-5 h-5 inline-block mr-2" />
                                    La cotización ha sido enviada al cliente.
                                </div>
                                <Link 
                                    :href="route('pedidos.create', { cotizacion_id: visita.id })" 
                                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors text-center block"
                                >
                                    Crear Pedido desde esta Cotización
                                </Link>
                            </div>
                            

                        </div>
                    </div>

                    <!-- Columna de Detalles -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Detalle de Productos</h3>
                            <div class="space-y-4">
                                <div v-for="producto in cotizacion.productos" :key="producto.id" class="p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold text-white">{{ producto.nombre }}</p>
                                            <p class="text-sm text-gray-400">{{ producto.descripcion }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0 ml-4">
                                            <p class="text-lg font-semibold text-white">${{ (producto.precio_base * producto.cantidad).toLocaleString('es-CL') }}</p>
                                            <p class="text-sm text-gray-400">{{ producto.cantidad }} x ${{ producto.precio_base.toLocaleString('es-CL') }}</p>
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
                                <span class="text-lg font-bold text-white">Total: ${{ cotizacion.total.toLocaleString('es-CL') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <ConfirmarEnvioModal
            :show="mostrarModalEnvio"
            :cliente="visita.cliente"
            :cotizacion="cotizacion"
            @close="mostrarModalEnvio = false"
            @confirm="confirmarEnvioCliente"
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
.btn-danger {
    @apply w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors flex items-center justify-center disabled:opacity-50;
}
.animate-spin-slow {
    animation: spin 3s linear infinite;
}
.loader {
    @apply w-4 h-4 border-2 border-t-transparent border-white rounded-full animate-spin;
}
</style> 