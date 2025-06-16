<template>
    <div class="min-h-screen bg-gray-100 py-6 flex flex-col justify-center sm:py-12">
        <div class="relative py-3 sm:max-w-xl sm:mx-auto">
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-light-blue-500 shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>
            <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">
                <div class="max-w-md mx-auto">
                    <div class="divide-y divide-gray-200">
                        <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
                            <h1 class="text-2xl font-bold text-gray-900">{{ message }}</h1>
                            <p>Esta es una página de prueba para verificar que Inertia.js funcione correctamente.</p>
                            
                            <div v-if="user">
                                <p><strong>Usuario autenticado:</strong> {{ user.name }}</p>
                                <p><strong>Email:</strong> {{ user.email }}</p>
                            </div>
                            <div v-else>
                                <p><strong>Estado:</strong> No autenticado</p>
                            </div>

                            <div class="mt-6">
                                <a href="/dashboard" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Ir al Dashboard
                                </a>
                                <a href="/login" class="ml-2 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    message: String,
    user: Object
});
</script> 