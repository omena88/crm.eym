<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, CheckCircleIcon, XCircleIcon, DocumentCheckIcon, InformationCircleIcon, PaperClipIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

const props = defineProps({
    visita: Object,
    cotizacion: Object,
    userRole: String,
});

const form = useForm({
    visita_id: props.visita.id,
    nuevo_estado: '',
});

const cambiarEstado = (nuevoEstado) => {
    form.nuevo_estado = nuevoEstado;
    form.post(route('cotizaciones.simularAccion'), {
        preserveScroll: true,
    });
};

const productosInternos = ref(
    props.cotizacion.productos.map(p => {
        const documentos = Array.from({ length: p.documentos_requeridos }, (_, i) => ({
            id: i,
            nombre: `Certificado ISO-900${i + 1} (Ejemplo)`,
            estado: i < p.documentos_subidos ? 'Subido' : 'Pendiente',
            file: i < p.documentos_subidos ? { name: `documento_producto_${p.id}_${i}.pdf` } : null,
        }));
        return { ...p, documentos };
    })
);

const todosDocumentosCompletos = computed(() => 
    productosInternos.value.every(p => p.documentos.every(d => d.estado === 'Subido'))
);

const simularCargaArchivo = (producto, documento) => {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.pdf,.doc,.docx,.jpg,.png';
    fileInput.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            documento.file = file;
            documento.estado = 'Subido';
        }
    };
    fileInput.click();
};

const quitarArchivo = (documento) => {
    documento.file = null;
    documento.estado = 'Pendiente';
};
</script>

<template>
    <Head title="Revisar Calidad de Cotización" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('cotizaciones.index')" class="text-gray-400 hover:text-white">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-100">
                        Revisión de Calidad
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Cotización para: <span class="font-medium text-white">{{ visita.cliente.razon_social }}</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="userRole !== 'calidad'" class="bg-red-900/80 border border-red-700 text-red-300 px-4 py-3 rounded-lg mb-6 text-center">
                    <p class="font-bold">Acceso Denegado</p>
                    <p class="text-sm">Esta vista solo está disponible para el rol de Calidad.</p>
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
                     <div class="p-6 border-b border-gray-800 bg-gray-950/50">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <DocumentCheckIcon class="w-6 h-6 mr-3 text-blue-400"/>
                            Gestión de Documentos de Calidad
                        </h3>
                    </div>

                    <div class="p-6 space-y-6">
                        <div v-for="producto in productosInternos" :key="producto.id" class="p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                            <p class="font-bold text-white text-lg mb-3">{{ producto.nombre }}</p>
                            <p class="text-sm text-gray-400 mb-4">Se requieren {{ producto.documentos_requeridos }} documentos para este producto.</p>
                            
                            <ul class="space-y-3">
                                <li v-for="documento in producto.documentos" :key="documento.id" class="p-3 bg-gray-900/70 rounded-md border border-gray-700/50 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <PaperClipIcon class="w-5 h-5 mr-3 text-gray-500"/>
                                        <span class="text-sm font-medium text-gray-300">{{ documento.nombre }}</span>
                                    </div>
                                    
                                    <div v-if="userRole === 'calidad'">
                                        <div v-if="documento.estado === 'Subido'" class="flex items-center space-x-3">
                                            <span class="text-xs text-green-400 bg-green-900/50 px-2 py-1 rounded-full truncate max-w-xs" :title="documento.file.name">{{ documento.file.name }}</span>
                                            <button @click="quitarArchivo(documento)" class="text-red-500 hover:text-red-400">
                                                <XCircleIcon class="w-5 h-5"/>
                                            </button>
                                        </div>
                                        <button v-else @click="simularCargaArchivo(producto, documento)" class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors">
                                            <ArrowUpTrayIcon class="w-4 h-4 mr-2"/>
                                            Subir archivo
                                        </button>
                                    </div>
                                     <div v-else>
                                         <span class="px-3 py-1 text-xs rounded-full" :class="documento.estado === 'Subido' ? 'bg-green-900/50 text-green-300' : 'bg-yellow-900/50 text-yellow-300'">
                                            {{ documento.estado }}
                                         </span>
                                     </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div v-if="userRole === 'calidad'" class="p-6 border-t border-gray-800">
                         <h3 class="text-lg font-semibold text-white mb-4 text-center">Decisión de Calidad</h3>
                        
                        <div v-if="!todosDocumentosCompletos" class="text-center p-3 rounded-lg bg-yellow-900/50 border border-yellow-800 text-yellow-300 text-sm mb-4">
                            <InformationCircleIcon class="w-5 h-5 inline-block mr-2" />
                            Debe completar la carga de todos los documentos para poder aprobar.
                        </div>

                        <div class="flex justify-center space-x-4">
                            <button @click="cambiarEstado('CalidadAprobada')" :disabled="!todosDocumentosCompletos" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:bg-gray-600 disabled:cursor-not-allowed">
                                <CheckCircleIcon class="w-5 h-5 mr-2" />
                                Aprobar Documentación
                            </button>
                            <button @click="cambiarEstado('CalidadRechazada')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <XCircleIcon class="w-5 h-5 mr-2" />
                                Rechazar
                            </button>
                        </div>
                         <p class="text-xs text-gray-500 mt-4 text-center">Si se rechaza, se solicitará al vendedor que revise la cotización.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 