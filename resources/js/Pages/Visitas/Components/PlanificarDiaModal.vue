<script setup>
import { ref, onMounted, computed } from 'vue';
import { PlusIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    fecha: Date,
    semana: Number,
    anio: Number
});

const emit = defineEmits(['close', 'guardado']);

const visitas = ref([]);
const clientes = ref([]);
const isLoading = ref(false);

// Formatear la fecha para mostrar en el título
const fechaFormateada = computed(() => {
    if (!props.fecha) return '';
    const options = { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    };
    return props.fecha.toLocaleDateString('es-ES', options);
});

// Obtener la fecha en formato ISO para el input
const fechaISO = computed(() => {
    if (!props.fecha) return '';
    return props.fecha.toISOString().split('T')[0];
});

// Inicializar con 2 líneas por defecto
function inicializarVisitas() {
    visitas.value = [
        { 
            cliente_id: null, 
            fecha: fechaISO.value,
            hora: '',
            objetivos: '', 
            tipo: 'comercial' 
        },
        { 
            cliente_id: null, 
            fecha: fechaISO.value,
            hora: '',
            objetivos: '', 
            tipo: 'comercial' 
        }
    ];
}

// Cargar clientes cuando el modal se monta
onMounted(async () => {
    inicializarVisitas();
    
    try {
        const response = await fetch('/clientes-list');
        if (!response.ok) throw new Error('Error al cargar clientes');
        clientes.value = await response.json();
    } catch (error) {
        console.error('No se pudieron cargar los clientes:', error);
        alert('No se pudieron cargar los clientes. Por favor, recarga la página.');
    }
});

function agregarVisita() {
    visitas.value.push({
        cliente_id: null,
        fecha: fechaISO.value,
        hora: '',
        objetivos: '',
        tipo: 'comercial'
    });
}

function eliminarVisita(index) {
    if (visitas.value.length > 1) {
        visitas.value.splice(index, 1);
    }
}

async function guardarVisitas() {
    // Filtrar visitas que tengan datos completos
    const visitasCompletas = visitas.value.filter(v => 
        v.cliente_id && v.objetivos.trim()
    );
    
    if (visitasCompletas.length === 0) {
        alert('Por favor completa al menos una visita con cliente y objetivos.');
        return;
    }

    isLoading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('/visitas/planificacion/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                semana: props.semana,
                año: props.anio,
                visitas: visitasCompletas.map(v => ({
                    ...v,
                    fecha_programada: v.fecha,
                    hora_planificada: v.hora || null
                }))
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al guardar las visitas.');
        }

        emit('guardado');
        emit('close');
        alert(`${visitasCompletas.length} visita(s) guardada(s) para el ${fechaFormateada.value}`);
        
    } catch (error) {
        console.error('Error guardando visitas:', error);
        alert(error.message);
    } finally {
        isLoading.value = false;
    }
}

</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50" @click.self="$emit('close')">
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col border border-gray-700">
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <h3 class="text-lg font-medium text-gray-100">
                    Planificar Visitas - {{ fechaFormateada }}
                </h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-300">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto">
                <div v-for="(visita, index) in visitas" :key="index" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center bg-gray-900/50 p-3 rounded-md">
                    <!-- Cliente -->
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Cliente</label>
                        <select v-model="visita.cliente_id" class="w-full bg-gray-800 border-gray-700 rounded-md text-gray-100 text-sm">
                            <option :value="null" disabled>Seleccione un cliente</option>
                            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                {{ cliente.razon_social }}
                            </option>
                        </select>
                    </div>

                    <!-- Hora -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Hora</label>
                        <input 
                            type="time" 
                            v-model="visita.hora" 
                            class="w-full bg-gray-800 border-gray-700 rounded-md text-gray-100 text-sm"
                        >
                    </div>

                    <!-- Objetivos -->
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Objetivos</label>
                        <input 
                            type="text" 
                            v-model="visita.objetivos" 
                            placeholder="Ej: Presentar nueva propuesta" 
                            class="w-full bg-gray-800 border-gray-700 rounded-md text-gray-100 text-sm"
                        >
                    </div>

                    <!-- Eliminar -->
                    <div class="md:col-span-1 flex items-end justify-center">
                         <button 
                            @click="eliminarVisita(index)" 
                            :disabled="visitas.length <= 1"
                            :class="[
                                'p-2 transition-colors',
                                visitas.length <= 1 
                                    ? 'text-gray-600 cursor-not-allowed' 
                                    : 'text-red-500 hover:text-red-400'
                            ]"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <button @click="agregarVisita" class="flex items-center space-x-2 text-sm text-blue-400 hover:text-blue-300">
                    <PlusIcon class="w-5 h-5" />
                    <span>Agregar Otra Visita</span>
                </button>
            </div>

            <div class="flex justify-end p-4 border-t border-gray-700 bg-gray-800/50">
                <SecondaryButton @click="$emit('close')" class="mr-3">Cancelar</SecondaryButton>
                <PrimaryButton @click="guardarVisitas" :disabled="isLoading">
                    {{ isLoading ? 'Guardando...' : 'Guardar Visitas' }}
                </PrimaryButton>
            </div>
        </div>
    </div>
</template> 