<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    cliente: Object,
    clientes: Object,
});

const form = useForm({
    cliente_id: props.cliente?.id || '',
    nombre: '',
    apellidos: '',
    puesto: '',
    telefono: '',
    celular: '',
    email: '',
    es_contacto_principal: false,
    notas: '',
});

const submit = () => {
    form.post(route('contactos.store'));
};
</script>

<template>
    <Head title="Nuevo Contacto" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100 mb-4">
                        Nuevo Contacto
                    </h2>
                    <!-- Pestañas -->
                    <nav class="flex space-x-8">
                        <Link 
                            :href="route('clientes.index')"
                            class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            Clientes
                        </Link>
                        <Link 
                            :href="route('contactos.index')"
                            class="border-blue-500 text-blue-400 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            Contactos
                        </Link>
                    </nav>
                </div>
                <Link 
                    :href="route('contactos.index')" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors"
                >
                    Volver a Contactos
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Cliente seleccionado (si viene de un cliente específico) -->
                <div v-if="cliente" class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-100 mb-4">Cliente</h3>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <UserIcon class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <p class="text-lg font-medium text-gray-100">{{ cliente.razon_social }}</p>
                            <p class="text-gray-400">{{ cliente.codigo }} • {{ cliente.sector }}</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Selección de Cliente (si no viene de un cliente específico) -->
                    <div v-if="!cliente" class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-100 mb-4">Seleccionar Cliente</h3>
                        <div>
                            <InputLabel for="cliente_id" value="Cliente *" class="text-gray-300" />
                            <select
                                id="cliente_id"
                                v-model="form.cliente_id"
                                class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100 rounded-md"
                            >
                                <option value="">Seleccione un cliente</option>
                                <option 
                                    v-for="cliente in clientes" 
                                    :key="cliente.id" 
                                    :value="cliente.id"
                                >
                                    {{ cliente.razon_social }} ({{ cliente.codigo }})
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.cliente_id" />
                        </div>
                    </div>

                    <!-- Información del Contacto -->
                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center mb-6">
                            <UserIcon class="w-6 h-6 text-green-400 mr-3" />
                            <h3 class="text-lg font-medium text-gray-100">Información del Contacto</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="nombre" value="Nombre *" class="text-gray-300" />
                                <TextInput
                                    id="nombre"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.nombre"
                                    placeholder="Juan"
                                />
                                <InputError class="mt-2" :message="form.errors.nombre" />
                            </div>

                            <div>
                                <InputLabel for="apellidos" value="Apellidos" class="text-gray-300" />
                                <TextInput
                                    id="apellidos"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.apellidos"
                                    placeholder="Pérez García"
                                />
                                <InputError class="mt-2" :message="form.errors.apellidos" />
                            </div>

                            <div>
                                <InputLabel for="puesto" value="Puesto/Cargo" class="text-gray-300" />
                                <TextInput
                                    id="puesto"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.puesto"
                                    placeholder="Gerente General"
                                />
                                <InputError class="mt-2" :message="form.errors.puesto" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" class="text-gray-300" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.email"
                                    placeholder="juan.perez@empresa.com"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="celular" value="Celular" class="text-gray-300" />
                                <TextInput
                                    id="celular"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.celular"
                                    placeholder="987654321"
                                />
                                <InputError class="mt-2" :message="form.errors.celular" />
                            </div>

                            <div>
                                <InputLabel for="telefono" value="Teléfono Fijo" class="text-gray-300" />
                                <TextInput
                                    id="telefono"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.telefono"
                                    placeholder="01-234-5678"
                                />
                                <InputError class="mt-2" :message="form.errors.telefono" />
                            </div>

                            <div class="md:col-span-2">
                                <div class="flex items-center">
                                    <input
                                        id="es_contacto_principal"
                                        type="checkbox"
                                        v-model="form.es_contacto_principal"
                                        class="h-4 w-4 text-blue-600 bg-gray-800 border-gray-700 rounded focus:ring-blue-500 focus:ring-2"
                                    />
                                    <InputLabel for="es_contacto_principal" value="Contacto Principal" class="ml-2 text-gray-300" />
                                </div>
                                <InputError class="mt-2" :message="form.errors.es_contacto_principal" />
                            </div>

                            <div class="md:col-span-2">
                                <InputLabel for="notas" value="Notas" class="text-gray-300" />
                                <textarea
                                    id="notas"
                                    v-model="form.notas"
                                    rows="3"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100 rounded-md"
                                    placeholder="Información adicional sobre el contacto..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notas" />
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-4">
                        <Link 
                            :href="route('contactos.index')"
                            class="px-6 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700"
                        >
                            {{ form.processing ? 'Creando...' : 'Crear Contacto' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 