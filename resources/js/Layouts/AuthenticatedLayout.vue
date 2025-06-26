<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    UserIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    ChevronDownIcon,
    Bars3Icon,
    XMarkIcon,
    BellIcon
} from '@heroicons/vue/24/outline';

const showingNavigationDropdown = ref(false);
const page = usePage();

// Componente funcional - layout de autenticación

// Función para cerrar el menú móvil
const closeNavigation = () => {
    showingNavigationDropdown.value = false;
};

// Definir las rutas de navegación
const navigationItems = [
    {
        name: 'Dashboard',
        href: 'dashboard',
        current: 'dashboard'
    },
    {
        name: 'Clientes',
        href: 'clientes.index',
        current: 'clientes.*'
    },
    {
        name: 'Visitas',
        href: 'visitas.index',
        current: 'visitas.*'
    },
    {
        name: 'Cotizaciones',
        href: '#',
        current: 'cotizaciones.*',
        disabled: true
    },
    {
        name: 'Aprobaciones',
        href: '#',
        current: 'aprobaciones.*',
        disabled: true
    }
];
</script>

<template>
    <div class="min-h-screen bg-black">
        <div class="min-h-screen bg-black">
            <nav class="bg-gray-950 border-b border-gray-800 sticky top-0 z-50">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-blue-600 rounded-md flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">C</span>
                                    </div>
                                    <span class="text-xl font-bold text-white hidden sm:block">CRM</span>
                                </Link>
                            </div>

                            <!-- Desktop Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                 <NavLink :href="route('clientes.index')" :active="route().current('clientes.index')">
                                    Clientes
                                </NavLink>
                                <NavLink :href="route('visitas.index')" :active="route().current('visitas.index')">
                                    Visitas
                                </NavLink>
                                <NavLink :href="route('cotizaciones.index')" :active="route().current('cotizaciones.index')">
                                    Cotizaciones
                                </NavLink>
                                <NavLink :href="route('pedidos.index')" :active="route().current('pedidos.index')">
                                    Pedidos
                                </NavLink>
                                <NavLink :href="route('productos.index')" :active="route().current('productos.index')">
                                    Productos
                                </NavLink>
                            </div>
                        </div>

                        <!-- Desktop User Menu -->
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Notificaciones -->
                            <button class="p-2 text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded-full transition-colors relative">
                                <BellIcon class="w-5 h-5" />
                                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                            </button>
                            
                            <!-- User Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="56" v-if="page.props.auth && page.props.auth.user">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-gray-700 text-sm leading-4 font-medium rounded-lg text-gray-300 bg-gray-800 hover:bg-gray-700 hover:text-gray-100 hover:border-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-blue-500 transition-all duration-200"
                                        >
                                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-white text-sm font-bold">
                                                    {{ page.props.auth.user.name?.charAt(0)?.toUpperCase() || 'U' }}
                                                </span>
                                            </div>
                                            <div class="text-left">
                                                <div class="font-medium text-gray-100">{{ page.props.auth.user.name || 'Usuario' }}</div>
                                                <div class="text-xs text-gray-400">{{ page.props.auth.user.rol || 'Usuario' }}</div>
                                            </div>
                                            <ChevronDownIcon class="ml-2 h-4 w-4" />
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="py-1 bg-gray-900 border border-gray-700 rounded-lg shadow-xl">
                                            <div class="px-4 py-3 border-b border-gray-700">
                                                <p class="text-sm text-gray-100 font-medium">{{ page.props.auth.user.name || 'Usuario' }}</p>
                                                <p class="text-xs text-gray-400">{{ page.props.auth.user.email || 'email@ejemplo.com' }}</p>
                                                <p class="text-xs text-blue-400 mt-1">{{ page.props.auth.user.rol || 'Usuario' }}</p>
                                            </div>
                                            <DropdownLink
                                                :href="route('profile.edit')"
                                                class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-gray-100 transition-colors"
                                            >
                                                <UserIcon class="w-4 h-4 mr-3" />
                                                Mi Perfil
                                            </DropdownLink>
                                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-gray-100 transition-colors">
                                                <Cog6ToothIcon class="w-4 h-4 mr-3" />
                                                Configuración
                                            </a>
                                            <div class="border-t border-gray-700 my-1"></div>
                                            <DropdownLink
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                                class="flex items-center w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-gray-800 hover:text-red-300 transition-colors"
                                            >
                                                <ArrowRightOnRectangleIcon class="w-4 h-4 mr-3" />
                                                Cerrar sesión
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                                
                                <!-- Login Button for Guest -->
                                <Link v-else :href="route('login')" class="inline-flex items-center px-4 py-2 border border-gray-700 text-sm font-medium rounded-lg text-gray-300 bg-gray-800 hover:bg-gray-700 hover:text-gray-100 transition-colors">
                                    Iniciar Sesión
                                </Link>
                            </div>
                        </div>

                        <!-- Mobile Hamburger -->
                        <div class="flex items-center sm:hidden">
                            <!-- Mobile Notification Bell -->
                            <button class="p-2 mr-2 text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded-full transition-colors relative">
                                <BellIcon class="w-5 h-5" />
                                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                            </button>
                            
                            <!-- Hamburger Button -->
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-300 hover:bg-gray-800 focus:outline-none focus:bg-gray-800 focus:text-gray-300 transition-all duration-200 transform hover:scale-105"
                            >
                                <Bars3Icon 
                                    v-if="!showingNavigationDropdown"
                                    class="h-6 w-6" 
                                />
                                <XMarkIcon 
                                    v-else
                                    class="h-6 w-6" 
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <div
                    :class="{ 
                        'block': showingNavigationDropdown, 
                        'hidden': !showingNavigationDropdown 
                    }"
                    class="sm:hidden absolute top-16 left-0 right-0 bg-gray-950 border-t border-gray-800 shadow-xl z-40"
                    style="min-height: calc(100vh - 4rem);"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                         <ResponsiveNavLink :href="route('clientes.index')" :active="route().current('clientes.index')">
                            Clientes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('visitas.index')" :active="route().current('visitas.index')">
                            Visitas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('cotizaciones.index')" :active="route().current('cotizaciones.index')">
                            Cotizaciones
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('pedidos.index')" :active="route().current('pedidos.index')">
                            Pedidos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('productos.index')" :active="route().current('productos.index')">
                            Productos
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-6 border-t border-gray-800" v-if="page.props.auth && page.props.auth.user">
                        <!-- User Info -->
                        <div class="px-4 mb-4">
                            <div class="flex items-center space-x-3 p-4 bg-gray-900 rounded-lg">
                                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-lg font-bold">
                                        {{ page.props.auth.user.name?.charAt(0)?.toUpperCase() || 'U' }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-base text-gray-100">
                                        {{ page.props.auth.user.name || 'Usuario' }}
                                    </div>
                                    <div class="font-medium text-sm text-gray-400">
                                        {{ page.props.auth.user.email || 'email@ejemplo.com' }}
                                    </div>
                                    <div class="font-medium text-xs text-blue-400 mt-1">
                                        {{ page.props.auth.user.rol || 'Usuario' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Actions -->
                        <div class="px-4 space-y-2">
                            <ResponsiveNavLink
                                :href="route('profile.edit')"
                                @click="closeNavigation"
                                class="flex items-center px-4 py-3 border-l-4 border-transparent text-base font-medium text-gray-400 hover:text-gray-300 hover:bg-gray-900/50 hover:border-gray-700 transition-all duration-200 rounded-r-lg"
                            >
                                <UserIcon class="w-5 h-5 mr-3" />
                                Mi Perfil
                            </ResponsiveNavLink>
                            <div class="flex items-center px-4 py-3 border-l-4 border-transparent text-base font-medium text-gray-400 hover:text-gray-300 hover:bg-gray-900/50 hover:border-gray-700 transition-all duration-200 rounded-r-lg cursor-pointer">
                                <Cog6ToothIcon class="w-5 h-5 mr-3" />
                                Configuración
                            </div>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                @click="closeNavigation"
                                class="flex items-center w-full text-left px-4 py-3 border-l-4 border-transparent text-base font-medium text-red-400 hover:text-red-300 hover:bg-gray-900/50 hover:border-red-700 transition-all duration-200 rounded-r-lg"
                            >
                                <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3" />
                                Cerrar sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <!-- Overlay para cerrar el menú -->
                    <div 
                        v-if="showingNavigationDropdown"
                        @click="closeNavigation"
                        class="fixed inset-0 bg-black bg-opacity-50 z-30"
                        style="top: 100vh;"
                    ></div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-gray-950 border-b border-gray-800" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
