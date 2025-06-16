<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { EnvelopeIcon, LockClosedIcon, EyeIcon, EyeSlashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const attemptCount = ref(0);

// Mensaje de error general
const generalError = computed(() => {
    if (form.errors.email && form.errors.password) {
        return 'Por favor verifica tu correo electrónico y contraseña';
    }
    if (form.errors.email || form.errors.password) {
        return 'Credenciales incorrectas. Verifica tu información';
    }
    if (attemptCount.value > 3) {
        return 'Múltiples intentos fallidos. Verifica tu información o solicita un restablecimiento de contraseña';
    }
    return null;
});

const submit = () => {
    attemptCount.value++;
    
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
        onError: (errors) => {
            console.error('Error en login:', errors);
            if (errors.email) {
                console.error('Error de email:', errors.email);
            }
            if (errors.password) {
                console.error('Error de password:', errors.password);
            }
        },
        onSuccess: () => {
            attemptCount.value = 0;
        }
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <!-- Título -->
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-100">Iniciar Sesión</h2>
            <p class="mt-2 text-sm text-gray-400">Ingresa a tu cuenta para continuar</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400 bg-green-900/20 border border-green-800 rounded-md p-3">
            {{ status }}
        </div>

        <!-- Error general -->
        <div v-if="generalError" class="mb-4 p-4 bg-red-900/20 border border-red-800 rounded-md">
            <div class="flex items-center">
                <ExclamationTriangleIcon class="w-5 h-5 text-red-400 mr-3" />
                <div>
                    <p class="text-sm font-medium text-red-400">Error de autenticación</p>
                    <p class="text-xs text-red-300 mt-1">{{ generalError }}</p>
                </div>
            </div>
        </div>

        <!-- Información de usuarios de prueba -->
        <div class="mb-6 p-4 bg-blue-900/20 border border-blue-800 rounded-md">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-400">Usuarios de prueba disponibles:</p>
                    <div class="mt-2 text-xs text-blue-300 space-y-1">
                        <div>• <span class="font-mono">admin@crm.local</span> / <span class="font-mono">password123</span></div>
                        <div>• <span class="font-mono">juan.perez@crm.local</span> / <span class="font-mono">password123</span></div>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-gray-300" />
                <div class="mt-1 relative">
                    <EnvelopeIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full pl-10 pr-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="tu@email.com"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Contraseña" class="text-gray-300" />
                <div class="mt-1 relative">
                    <LockClosedIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pl-10 pr-10 py-2 bg-gray-800 border border-gray-700 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="Tu contraseña"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-300 transition-colors"
                    >
                        <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                        <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox 
                        name="remember" 
                        v-model:checked="form.remember"
                        class="w-4 h-4 text-blue-600 bg-gray-800 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                    />
                    <span class="ml-2 text-sm text-gray-300">Recordarme</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-400 hover:text-blue-300 transition-colors"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <!-- Submit button -->
            <div>
                <PrimaryButton
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="form.processing">Autenticando...</span>
                    <span v-else>INICIAR SESIÓN</span>
                </PrimaryButton>
            </div>

            <!-- Estado de conexión -->
            <div v-if="form.processing" class="text-center">
                <p class="text-xs text-gray-400 mt-2">Verificando credenciales...</p>
            </div>

            <!-- Register link -->
            <div class="text-center">
                <p class="text-sm text-gray-400">
                    ¿No tienes una cuenta?
                    <Link
                        :href="route('register')"
                        class="font-medium text-blue-400 hover:text-blue-300 transition-colors"
                    >
                        Regístrate aquí
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
