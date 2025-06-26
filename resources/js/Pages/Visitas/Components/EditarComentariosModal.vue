<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    XMarkIcon,
    CalendarDaysIcon,
    BuildingOfficeIcon,
    UserIcon,
    ClockIcon,
    MapPinIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    visita: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
    comentarios: props.visita.comentarios || ''
});

const submit = () => {
    form.put(route('visitas.update-comentarios', props.visita.id), {
        onSuccess: () => {
            emit('updated');
            emit('close');
        }
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatTime = (time) => {
    if (!time) return 'No especificada';
    return new Date('2000-01-01 ' + time).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getEstadoBadgeColor = (estado) => {
    const colors = {
        'pendiente': 'bg-yellow-100 text-yellow-800',
        'programada': 'bg-blue-100 text-blue-800',
        'aprobada': 'bg-green-100 text-green-800',
        'realizada': 'bg-purple-100 text-purple-800',
        'cancelada': 'bg-red-100 text-red-800'
    };
    return colors[estado] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Modal @close="emit('close')" max-width="4xl">
        <div class="bg-gray-900 rounded-lg">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-800">
                <h3 class="text-lg font-semibold text-gray-100">
                    Editar Comentarios de Visita
                </h3>
                <button
                    @click="emit('close')"
                    class="text-gray-400 hover:text-gray-300 transition-colors"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Información de la visita (solo lectura) -->
            <div class="p-6 border-b border-gray-800">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Información básica -->
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-400 mb-2">Información de la Visita</h4>
                            <div class="bg-gray-800 rounded-lg p-4">
                                <h5 class="text-lg font-semibold text-gray-100 mb-2">
                                    {{ visita.titulo }}
                                </h5>
                                <p v-if="visita.descripcion" class="text-gray-300 mb-3">
                                    {{ visita.descripcion }}
                                </p>
                                <div class="flex items-center space-x-4">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getEstadoBadgeColor(visita.estado)"
                                    >
                                        {{ visita.estado.charAt(0).toUpperCase() + visita.estado.slice(1) }}
                                    </span>
                                    <span class="text-sm text-gray-400">
                                        Tipo: {{ visita.tipo === 'planificada' ? 'Planificada' : 'No Planificada' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Cliente -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-400 mb-2">Cliente</h4>
                            <div class="bg-gray-800 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <BuildingOfficeIcon class="w-5 h-5 text-gray-400 mt-1 flex-shrink-0" />
                                    <div>
                                        <h5 class="font-medium text-gray-100">
                                            {{ visita.cliente.razon_social }}
                                        </h5>
                                        <p v-if="visita.cliente.nombre_comercial" class="text-sm text-gray-400">
                                            {{ visita.cliente.nombre_comercial }}
                                        </p>
                                        <p v-if="visita.cliente.direccion" class="text-sm text-gray-400 flex items-center mt-1">
                                            <MapPinIcon class="w-4 h-4 mr-1" />
                                            {{ visita.cliente.direccion }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Programación -->
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-400 mb-2">Programación</h4>
                            <div class="bg-gray-800 rounded-lg p-4 space-y-3">
                                <div class="flex items-center space-x-3">
                                    <CalendarDaysIcon class="w-5 h-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-100">Fecha</p>
                                        <p class="text-sm text-gray-300">
                                            {{ formatDate(visita.fecha_programada) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <ClockIcon class="w-5 h-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-100">Hora</p>
                                        <p class="text-sm text-gray-300">
                                            {{ formatTime(visita.hora_planificada) }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="visita.duracion_estimada" class="flex items-center space-x-3">
                                    <ClockIcon class="w-5 h-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-100">Duración Estimada</p>
                                        <p class="text-sm text-gray-300">
                                            {{ visita.duracion_estimada }} minutos
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Objetivos (si existen) -->
                        <div v-if="visita.objetivos">
                            <h4 class="text-sm font-medium text-gray-400 mb-2">Objetivos</h4>
                            <div class="bg-gray-800 rounded-lg p-4">
                                <p class="text-sm text-gray-300">{{ visita.objetivos }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario para editar comentarios -->
            <div class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="comentarios" value="Comentarios" class="text-gray-300" />
                        <textarea
                            id="comentarios"
                            v-model="form.comentarios"
                            rows="6"
                            class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Agrega comentarios adicionales sobre esta visita..."
                        ></textarea>
                        <p class="mt-2 text-sm text-gray-400">
                            Los comentarios son la única información que puedes modificar en visitas planificadas o aprobadas.
                        </p>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-4">
                        <SecondaryButton @click="emit('close')" type="button">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton 
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700"
                        >
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Comentarios</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template> 