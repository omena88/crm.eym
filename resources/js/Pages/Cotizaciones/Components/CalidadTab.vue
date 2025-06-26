<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { DocumentArrowUpIcon, EyeIcon, CheckCircleIcon, XCircleIcon, DocumentCheckIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    cotizacion: {
        type: Object,
        required: true
    },
    visitaId: {
        type: [Number, String],
        required: true
    },
    userRole: String,
    estadoFlujo: String,
});

const emit = defineEmits(['update:calidad']);

// Estado local para simular la carga de documentos
const productosState = ref(props.cotizacion.productos.map(p => ({ ...p, subiendo: false })));

const todosDocumentosSubidos = computed(() => {
    return productosState.value.every(p => p.documentos_subidos >= p.documentos_requeridos);
});

const simularSubida = (producto) => {
    const p = productosState.value.find(pr => pr.id === producto.id);
    if (p && p.documentos_subidos < p.documentos_requeridos) {
        p.subiendo = true;
        setTimeout(() => {
            p.documentos_subidos++;
            p.subiendo = false;
        }, 1500);
    }
};

const aprobarCalidad = () => {
    if (!confirm('¿Está seguro de aprobar la documentación y dejar la cotización lista para enviar al cliente?')) {
        return;
    }

    router.post(route('cotizaciones.simularAccion'), {
        visita_id: props.visitaId,
        nuevo_estado: 'Listo para Enviar',
    }, {
        preserveState: false,
    });
};

const puedeEditar = computed(() => {
    return props.userRole === 'calidad' && props.estadoFlujo === 'RevisionCalidad';
});

const productosInternos = ref(
    props.cotizacion.productos.map(p => ({
        ...p,
        documentos_subidos: ref(p.documentos_subidos),
        aprobado_calidad: ref(false),
    }))
);

const todosDocumentosCompletos = computed(() => 
    productosInternos.value.every(p => p.documentos_subidos >= p.documentos_requeridos)
);

const simulacionSubirDocumento = (producto) => {
    if (producto.documentos_subidos < producto.documentos_requeridos) {
        producto.documentos_subidos++;
    }
};

const simulacionQuitarDocumento = (producto) => {
    if (producto.documentos_subidos > 0) {
        producto.documentos_subidos--;
    }
}

</script>

<template>
    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-100">
                Aprobación de Calidad
            </h3>
            <span class="px-3 py-1 text-sm font-medium rounded-full"
                :class="{ 'bg-yellow-500 text-yellow-900': !todosDocumentosSubidos, 'bg-green-500 text-green-900': todosDocumentosSubidos }">
                {{ todosDocumentosSubidos ? 'Documentación Completa' : 'Pendiente de Documentos' }}
            </span>
        </div>

        <div class="space-y-4">
            <div v-for="producto in productosState" :key="producto.id" class="bg-gray-800 p-4 rounded-lg flex items-center justify-between">
                <div>
                    <h4 class="font-semibold text-gray-200">{{ producto.nombre }}</h4>
                    <p class="text-sm text-gray-400">{{ producto.descripcion }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                         <div class="flex items-center gap-2 text-sm font-semibold"
                            :class="{'text-green-400': producto.documentos_subidos >= producto.documentos_requeridos, 'text-yellow-400': producto.documentos_subidos < producto.documentos_requeridos}"
                        >
                            <CheckCircleIcon v-if="producto.documentos_subidos >= producto.documentos_requeridos" class="w-5 h-5" />
                            <XCircleIcon v-else class="w-5 h-5" />
                            <span>{{ producto.documentos_subidos }} / {{ producto.documentos_requeridos }} documentos</span>
                        </div>
                        <p class="text-xs text-gray-500">Documentos requeridos</p>
                    </div>

                    <button @click="simularSubida(producto)"
                        :disabled="producto.subiendo || producto.documentos_subidos >= producto.documentos_requeridos"
                        class="inline-flex items-center px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <DocumentArrowUpIcon class="w-4 h-4 mr-1" />
                        {{ producto.subiendo ? 'Subiendo...' : 'Subir Documento' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-6 text-right">
             <button @click="aprobarCalidad"
                :disabled="!todosDocumentosSubidos"
                class="inline-flex items-center px-6 py-3 text-base font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Aprobar y Dejar Listo para Enviar
            </button>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold text-white mb-2">Revisión de Documentación de Calidad</h3>
        <p v-if="!puedeEditar" class="text-sm text-yellow-400 bg-yellow-900/50 border border-yellow-800 rounded-lg p-3 mb-4 flex items-center">
             <InformationCircleIcon class="w-5 h-5 mr-2" />
            La gestión de documentos de calidad solo está activa durante el estado "Revisión de Calidad" y para el rol de Calidad.
        </p>
        
        <div class="space-y-4">
            <div v-for="producto in productosInternos" :key="producto.id" class="p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                <p class="font-bold text-white mb-2">{{ producto.nombre }}</p>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">
                       Progreso: {{ producto.documentos_subidos }} / {{ producto.documentos_requeridos }} documentos
                    </p>
                    <div v-if="puedeEditar" class="flex items-center space-x-2">
                        <button @click="simulacionQuitarDocumento(producto)" class="px-2 py-1 bg-red-600 hover:bg-red-700 rounded-md text-white">-</button>
                        <button @click="simulacionSubirDocumento(producto)" class="px-2 py-1 bg-green-600 hover:bg-green-700 rounded-md text-white">+</button>
                    </div>
                </div>
                <div class="mt-2 w-full bg-gray-700 rounded-full h-2.5">
                    <div class="bg-blue-500 h-2.5 rounded-full transition-all duration-300" :style="{ width: (producto.documentos_subidos / producto.documentos_requeridos) * 100 + '%' }"></div>
                </div>
                <div class="mt-2 text-sm font-semibold" :class="{
                    'text-green-400': producto.documentos_subidos >= producto.documentos_requeridos,
                    'text-yellow-400': producto.documentos_subidos < producto.documentos_requeridos,
                }">
                    {{ producto.documentos_subidos >= producto.documentos_requeridos ? 'Documentación Completa' : 'Documentación Pendiente' }}
                </div>
            </div>
        </div>

         <div v-if="puedeEditar" class="mt-6 p-4 bg-gray-800 rounded-lg text-center">
            <p v-if="!todosDocumentosCompletos" class="text-yellow-400">
                Aún falta subir documentación para uno o más productos.
            </p>
             <p v-else class="text-green-400 flex items-center justify-center">
                 <DocumentCheckIcon class="w-5 h-5 mr-2" />
                 Toda la documentación está completa. Ya puede aprobar desde el panel de acciones.
             </p>
        </div>
    </div>
</template> 