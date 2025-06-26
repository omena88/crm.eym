<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    XMarkIcon,
    CheckCircleIcon,
    ClockIcon,
    CalendarIcon,
    BuildingOfficeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    visita: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'visitaRealizada']);

const form = ref({
    comentarios: '',
    resultado: '',
    duracion_real: '',
    satisfaccion_cliente: 5,
    objetivos_alcanzados: false,
    requiere_seguimiento: false,
    fecha_siguiente_contacto: ''
});

const submitting = ref(false);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
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

const submitForm = async () => {
    submitting.value = true;
    
    try {
        const response = await fetch(`/visitas/${props.visita.id}/realizar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                ...form.value,
                fecha_realizada: new Date().toISOString()
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            emit('visitaRealizada');
        } else {
            alert(data.message || 'Error al realizar la visita');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al realizar la visita');
    } finally {
        submitting.value = false;
    }
};

const close = () => {
    emit('close');
};
</script>

<template>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click="close">
        <!-- Modal -->
        <div class="bg-gray-900 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-700" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-700">
                <h3 class="text-xl font-semibold text-gray-100">
                    Realizar Visita
                </h3>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-200 transition-colors"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>
            
            <!-- Información de la visita -->
            <div class="p-6 border-b border-gray-700 bg-gray-800">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-lg font-medium text-gray-100 mb-2">{{ visita.titulo }}</h4>
                        <div class="flex items-center text-gray-300 mb-1">
                            <BuildingOfficeIcon class="w-4 h-4 mr-2" />
                            <span class="text-sm">{{ visita.cliente.razon_social }}</span>
                        </div>
                        <div v-if="visita.descripcion" class="text-sm text-gray-400">
                            {{ visita.descripcion }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center text-gray-300">
                            <CalendarIcon class="w-4 h-4 mr-2" />
                            <span class="text-sm">{{ formatDate(visita.fecha_programada) }}</span>
                        </div>
                        <div v-if="visita.hora_planificada" class="flex items-center text-gray-300">
                            <ClockIcon class="w-4 h-4 mr-2" />
                            <span class="text-sm">{{ formatTime(visita.hora_planificada) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulario -->
            <form @submit.prevent="submitForm" class="p-6 space-y-6">
                <!-- Resultado de la visita -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Resultado de la Visita *
                    </label>
                    <textarea
                        v-model="form.resultado"
                        rows="4"
                        required
                        placeholder="Describe el resultado de la visita, acuerdos alcanzados, etc."
                        class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    ></textarea>
                </div>
                
                <!-- Comentarios adicionales -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Comentarios Adicionales
                    </label>
                    <textarea
                        v-model="form.comentarios"
                        rows="3"
                        placeholder="Comentarios, observaciones o notas adicionales..."
                        class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    ></textarea>
                </div>
                
                <!-- Duración real y satisfacción -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Duración Real (minutos)
                        </label>
                        <input
                            v-model="form.duracion_real"
                            type="number"
                            min="1"
                            max="480"
                            placeholder="60"
                            class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Satisfacción del Cliente (1-10)
                        </label>
                        <select
                            v-model="form.satisfaccion_cliente"
                            class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                </div>
                
                <!-- Checkboxes -->
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input
                            v-model="form.objetivos_alcanzados"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 bg-gray-800 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                        />
                        <span class="ml-2 text-sm text-gray-300">Se alcanzaron los objetivos de la visita</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input
                            v-model="form.requiere_seguimiento"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 bg-gray-800 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                        />
                        <span class="ml-2 text-sm text-gray-300">Requiere seguimiento</span>
                    </label>
                </div>
                
                <!-- Fecha de siguiente contacto -->
                <div v-if="form.requiere_seguimiento">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Fecha de Siguiente Contacto
                    </label>
                    <input
                        v-model="form.fecha_siguiente_contacto"
                        type="date"
                        class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <!-- Botones -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-md transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <CheckCircleIcon class="w-4 h-4 mr-2" />
                        {{ submitting ? 'Procesando...' : 'Marcar como Realizada' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template> 