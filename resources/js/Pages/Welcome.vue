<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    BuildingOfficeIcon,
    UserGroupIcon,
    PhoneIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    CheckCircleIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';
import { 
    UserIcon,
    EnvelopeIcon,
    PhoneIcon as PhoneIconSolid
} from '@heroicons/vue/24/solid';

// Datos de ejemplo para mostrar
const features = [
    {
        icon: UserGroupIcon,
        title: 'Gestión de Clientes',
        description: 'CRUD completo con 8 clientes de prueba',
        color: 'text-blue-400'
    },
    {
        icon: PhoneIcon,
        title: 'Contactos',
        description: '12 contactos vinculados listos',
        color: 'text-green-400'
    },
    {
        icon: CalendarDaysIcon,
        title: 'Visitas',
        description: '9 visitas programadas y realizadas',
        color: 'text-purple-400'
    },
    {
        icon: ChartBarIcon,
        title: 'Dashboard',
        description: 'Métricas y estadísticas en tiempo real',
        color: 'text-yellow-400'
    }
];

const demoUsers = [
    { email: 'admin@crm.local', password: 'password123' },
    { email: 'juan.perez@crm.local', password: 'password123' },
    { email: 'maria.rodriguez@crm.local', password: 'password123' }
];
</script>

<template>
    <Head title="Bienvenido - CRM EYM" />

    <div class="min-h-screen bg-gray-950 flex flex-col">
        <!-- Header -->
        <header class="bg-gray-900 border-b border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <BuildingOfficeIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">CRM EYM</h1>
                            <p class="text-xs text-gray-400">Sistema de Gestión</p>
                        </div>
                    </div>

                    <!-- Botones de navegación -->
                    <div class="flex items-center space-x-4">
                        <Link 
                            :href="route('login')"
                            class="inline-flex items-center px-4 py-2 border border-gray-700 text-sm font-medium rounded-md text-gray-300 bg-gray-800 hover:bg-gray-700 hover:text-gray-100 transition-colors"
                        >
                            Iniciar Sesión
                        </Link>
                        <Link 
                            :href="route('register')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-sm font-medium rounded-md text-white hover:bg-blue-700 transition-colors"
                        >
                            Registrarse
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Hero Section -->
            <section class="py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="mb-8">
                        <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <BuildingOfficeIcon class="w-12 h-12 text-white" />
                        </div>
                        <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                            CRM EYM
                        </h1>
                        <p class="text-xl text-gray-400 mb-8 max-w-3xl mx-auto">
                            Sistema de Gestión de Relaciones con Clientes
                        </p>
                    </div>

                    <!-- Status Badge -->
                    <div class="inline-flex items-center px-6 py-3 bg-green-900/20 border border-green-800 rounded-lg mb-12">
                        <CheckCircleIcon class="w-5 h-5 text-green-400 mr-2" />
                        <span class="text-green-400 font-medium">Sistema Funcionando Correctamente</span>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                        <Link 
                            :href="route('login')"
                            class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white text-lg font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Iniciar Sesión
                            <ArrowRightIcon class="w-5 h-5 ml-2" />
                        </Link>
                        <Link 
                            :href="route('register')"
                            class="inline-flex items-center justify-center px-8 py-4 border border-gray-700 text-gray-300 text-lg font-medium rounded-lg hover:bg-gray-800 transition-colors"
                        >
                            Crear Cuenta Nueva
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-20 bg-gray-900/50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold text-white mb-4">Características Principales</h2>
                        <p class="text-gray-400 text-lg">Backend Laravel configurado con SQLite y datos de prueba cargados</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div 
                            v-for="feature in features" 
                            :key="feature.title"
                            class="bg-gray-900 border border-gray-800 rounded-lg p-6 hover:bg-gray-800/50 transition-colors"
                        >
                            <component 
                                :is="feature.icon" 
                                :class="['w-12 h-12 mb-4', feature.color]"
                            />
                            <h3 class="text-lg font-semibold text-white mb-2">{{ feature.title }}</h3>
                            <p class="text-gray-400">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Demo Users Section -->
            <section class="py-20">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-white mb-4">Usuarios de Prueba</h2>
                            <p class="text-gray-400">Utiliza cualquiera de estas credenciales para probar el sistema</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div 
                                v-for="(user, index) in demoUsers" 
                                :key="index"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg p-4"
                            >
                                <div class="flex items-center mb-3">
                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                                        <UserIcon class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ user.email.split('@')[0] }}</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm">
                                        <EnvelopeIcon class="w-4 h-4 text-gray-400 mr-2" />
                                        <span class="text-gray-300">{{ user.email }}</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <span class="w-4 h-4 text-gray-400 mr-2">🔑</span>
                                        <span class="text-gray-300">{{ user.password }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-8">
                            <Link 
                                :href="route('login')"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Probar Ahora
                                <ArrowRightIcon class="w-4 h-4 ml-2" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center">
                    <p class="text-gray-400 text-sm">
                        © 2024 CRM EYM. Todos los derechos reservados.
                    </p>
                    <p class="text-gray-500 text-xs mt-2">
                        Laravel {{ $page.props.laravelVersion || 'v10' }} • PHP {{ $page.props.phpVersion || '8.x' }}
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
