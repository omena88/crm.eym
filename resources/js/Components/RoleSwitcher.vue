<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronDownIcon, UserIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    user: Object
});

const rolesDisponibles = ref({});
const rolActual = ref('');
const rolOriginal = ref(null);
const showDropdown = ref(false);
const isLoading = ref(false);

// Solo mostrar si es admin
const isAdmin = props.user?.rol === 'admin' || props.user?.rol_original === 'admin';

const obtenerRoles = async () => {
    if (!isAdmin) return;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('/admin/roles-disponibles', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || ''
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            rolesDisponibles.value = data.roles;
            rolActual.value = data.rol_actual;
            rolOriginal.value = data.rol_original;
        } else {
            console.error('Error obteniendo roles - Status:', response.status);
            // En caso de error, usar valores por defecto
            rolesDisponibles.value = {
                'vendedor': 'Vendedor',
                'gerente': 'Gerente',
                'calidad': 'Calidad',
                'admin': 'Administrador'
            };
            rolActual.value = props.user?.rol || 'vendedor';
            rolOriginal.value = props.user?.rol_original || null;
        }
    } catch (error) {
        console.error('Error obteniendo roles:', error);
        // En caso de error de red, usar valores por defecto
        rolesDisponibles.value = {
            'vendedor': 'Vendedor',
            'gerente': 'Gerente',
            'calidad': 'Calidad',
            'admin': 'Administrador'
        };
        rolActual.value = props.user?.rol || 'vendedor';
        rolOriginal.value = props.user?.rol_original || null;
    }
};

const cambiarRol = async (nuevoRol) => {
    if (nuevoRol === rolActual.value) {
        showDropdown.value = false;
        return;
    }
    
    isLoading.value = true;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            alert('Error: No se pudo obtener el token CSRF');
            return;
        }

        const response = await fetch('/admin/cambiar-rol', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ rol: nuevoRol })
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                // Recargar la página para aplicar el cambio de rol
                window.location.reload();
            } else {
                alert(data.message || 'Error al cambiar rol');
            }
        } else {
            console.error('Response status:', response.status);
            try {
                const errorData = await response.json();
                console.error('Error data:', errorData);
                alert(errorData.message || `Error al cambiar rol (${response.status})`);
            } catch (parseError) {
                const errorText = await response.text();
                console.error('Raw error response:', errorText);
                alert(`Error al cambiar rol (${response.status}): ${errorText.substring(0, 100)}`);
            }
        }
    } catch (error) {
        console.error('Error cambiando rol:', error);
        alert('Error al cambiar rol');
    } finally {
        isLoading.value = false;
        showDropdown.value = false;
    }
};

const restaurarRolOriginal = async () => {
    if (!rolOriginal.value) return;
    
    isLoading.value = true;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            alert('Error: No se pudo obtener el token CSRF');
            return;
        }

        const response = await fetch('/admin/restaurar-rol', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            // Recargar la página para aplicar el cambio
            window.location.reload();
        } else {
            const errorData = await response.json();
            alert(errorData.message || 'Error al restaurar rol');
        }
    } catch (error) {
        console.error('Error restaurando rol:', error);
        alert('Error al restaurar rol');
    } finally {
        isLoading.value = false;
    }
};

const getRolColor = (rol) => {
    const colors = {
        admin: 'text-purple-400',
        gerente: 'text-blue-400',
        vendedor: 'text-green-400',
        calidad: 'text-yellow-400'
    };
    return colors[rol] || 'text-gray-400';
};

const getRolIcon = (rol) => {
    // Puedes personalizar iconos por rol si lo deseas
    return UserIcon;
};

onMounted(() => {
    if (isAdmin) {
        obtenerRoles();
    }
});
</script>

<template>
    <div v-if="isAdmin" class="relative">
        <!-- Botón principal -->
        <button
            @click="showDropdown = !showDropdown"
            class="flex items-center space-x-2 px-3 py-2 rounded-md text-sm transition-colors bg-gray-800 hover:bg-gray-700 border border-gray-600"
            :disabled="isLoading"
        >
            <component :is="getRolIcon(rolActual)" class="w-4 h-4" />
            <span :class="getRolColor(rolActual)" class="font-medium">
                {{ rolesDisponibles[rolActual] || rolActual }}
            </span>
            <span v-if="rolOriginal" class="text-xs text-gray-500">(Admin)</span>
            <ChevronDownIcon 
                class="w-4 h-4 text-gray-400 transition-transform"
                :class="{ 'rotate-180': showDropdown }"
            />
        </button>

        <!-- Dropdown -->
        <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-56 bg-gray-800 border border-gray-600 rounded-lg shadow-lg z-50"
            @click.stop
        >
            <div class="py-2">
                <!-- Header del dropdown -->
                <div class="px-4 py-2 border-b border-gray-600">
                    <p class="text-xs text-gray-400 font-medium">Cambiar rol temporal</p>
                    <p v-if="rolOriginal" class="text-xs text-purple-300 mt-1">
                        Rol original: {{ rolesDisponibles[rolOriginal] }}
                    </p>
                </div>

                <!-- Lista de roles -->
                <div class="max-h-48 overflow-y-auto">
                    <button
                        v-for="(nombre, rol) in rolesDisponibles"
                        :key="rol"
                        @click="cambiarRol(rol)"
                        class="w-full flex items-center space-x-3 px-4 py-2 text-sm text-left hover:bg-gray-700 transition-colors"
                        :class="{
                            'bg-gray-700': rol === rolActual,
                            'opacity-50': isLoading
                        }"
                        :disabled="isLoading || rol === rolActual"
                    >
                        <component :is="getRolIcon(rol)" class="w-4 h-4" />
                        <span :class="getRolColor(rol)">{{ nombre }}</span>
                        <span v-if="rol === rolActual" class="ml-auto text-xs text-green-400">
                            ✓ Actual
                        </span>
                    </button>
                </div>

                <!-- Restaurar rol original -->
                <div v-if="rolOriginal" class="border-t border-gray-600 mt-2 pt-2">
                    <button
                        @click="restaurarRolOriginal"
                        class="w-full flex items-center space-x-3 px-4 py-2 text-sm text-left hover:bg-gray-700 transition-colors text-purple-400"
                        :disabled="isLoading"
                    >
                        <ArrowPathIcon class="w-4 h-4" />
                        <span>Restaurar rol Admin</span>
                    </button>
                </div>

                <!-- Info adicional -->
                <div class="border-t border-gray-600 mt-2 pt-2 px-4 py-2">
                    <p class="text-xs text-gray-500">
                        Los cambios de rol son temporales y solo para pruebas.
                    </p>
                </div>
            </div>
        </div>

        <!-- Overlay para cerrar dropdown -->
        <div
            v-if="showDropdown"
            class="fixed inset-0 z-40"
            @click="showDropdown = false"
        ></div>
    </div>
</template>

<style scoped>
/* Animaciones suaves para el dropdown */
.v-enter-active, .v-leave-active {
    transition: all 0.2s ease;
}
.v-enter-from, .v-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style> 