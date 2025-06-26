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
    rol: '',
});

const showPassword = ref(false);
const attemptCount = ref(0);

// Mensaje de error general
const generalError = computed(() => {
    if (form.errors.rol) {
        return 'Por favor selecciona un rol válido';
    }
    if (attemptCount.value > 3) {
        return 'Múltiples intentos fallidos. Verifica tu información o solicita un restablecimiento de contraseña';
    }
    return null;
});

const submit = (rol) => {
    attemptCount.value++;
    form.rol = rol;
    form.post(route('login'), {
        onFinish: () => {
            form.reset();
            attemptCount.value = 0;
        },
        onError: (errors) => {
            console.error('Error en login:', errors);
            if (errors.rol) {
                console.error('Error de rol:', errors.rol);
            }
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-700">Bienvenido a la Demo</h1>
            <p class="text-gray-500">Selecciona un rol para iniciar sesión</p>
        </div>

        <form @submit.prevent>
            <InputError class="mt-2" :message="form.errors.rol" />
            <div class="space-y-4">
                <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="submit('vendedor')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                    </svg>
                    Ingresar como Vendedor
                </PrimaryButton>

                <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="submit('gerente')">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        <path d="M12.293 8.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L13 10.414V15a1 1 0 11-2 0v-4.586l-2.293 2.293a1 1 0 01-1.414-1.414l3-3z" />
                    </svg>
                    Ingresar como Gerente
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
