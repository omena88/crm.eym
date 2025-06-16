<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { BuildingOfficeIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-950 pt-6 sm:justify-center sm:pt-0">
        <!-- Header con logo -->
        <div class="mb-8">
            <Link href="/" class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">C</span>
                </div>
                <span class="text-gray-100 font-semibold text-2xl">CRM</span>
            </Link>
        </div>

        <!-- Card del formulario -->
        <div class="w-full max-w-md">
            <div class="bg-gray-900 border border-gray-800 rounded-lg shadow-xl px-8 py-8">
                <slot />
            </div>
            
            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    © 2024 CRM Sistema. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</template>
