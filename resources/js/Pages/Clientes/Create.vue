<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BuildingOfficeIcon, UserIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    estados: Object,
    sectores: Object,
});

const form = useForm({
    ruc: '',
    razon_social: '',
    sector: '',
    estado: 'Pendiente',
    telefono: '',
    website: '',
    direccion: '',
    notas: '',
    contacto_principal: {
        nombre: '',
        apellidos: '',
        puesto: '',
        email: '',
        celular: '',
    }
});

const submit = () => {
    form.post(route('clientes.store'));
};
</script>

<template>
    <Head title="Nuevo Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100 mb-4">
                        Nuevo Cliente
                    </h2>
                    <!-- Pestañas -->
                    <nav class="flex space-x-8">
                        <Link 
                            :href="route('clientes.index')"
                            class="border-blue-500 text-blue-400 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            Clientes
                        </Link>
                        <Link 
                            :href="route('contactos.index')"
                            class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            Contactos
                        </Link>
                    </nav>
                </div>
                <Link 
                    :href="route('clientes.index')" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors"
                >
                    Volver a Clientes
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Información del Cliente -->
                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center mb-6">
                            <BuildingOfficeIcon class="w-6 h-6 text-blue-400 mr-3" />
                            <h3 class="text-lg font-medium text-gray-100">Información del Cliente</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="ruc" value="RUC *" class="text-gray-300" />
                                <TextInput
                                    id="ruc"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.ruc"
                                    placeholder="20123456789"
                                    maxlength="11"
                                />
                                <InputError class="mt-2" :message="form.errors.ruc" />
                            </div>

                            <div>
                                <InputLabel for="razon_social" value="Razón Social *" class="text-gray-300" />
                                <TextInput
                                    id="razon_social"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.razon_social"
                                    placeholder="Empresa S.A.C."
                                />
                                <InputError class="mt-2" :message="form.errors.razon_social" />
                            </div>

                            <div>
                                <InputLabel for="sector" value="Sector *" class="text-gray-300" />
                                <select
                                    id="sector"
                                    v-model="form.sector"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100 rounded-md"
                                >
                                    <option value="">Seleccione un sector</option>
                                    <option v-for="(label, value) in sectores" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.sector" />
                            </div>

                            <div>
                                <InputLabel for="estado" value="Estado *" class="text-gray-300" />
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100 rounded-md"
                                >
                                    <option v-for="(label, value) in estados" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.estado" />
                            </div>

                            <div>
                                <InputLabel for="telefono" value="Teléfono" class="text-gray-300" />
                                <TextInput
                                    id="telefono"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.telefono"
                                    placeholder="01-234-5678"
                                />
                                <InputError class="mt-2" :message="form.errors.telefono" />
                            </div>

                            <div>
                                <InputLabel for="website" value="Sitio Web" class="text-gray-300" />
                                <TextInput
                                    id="website"
                                    type="url"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.website"
                                    placeholder="https://www.empresa.com"
                                />
                                <InputError class="mt-2" :message="form.errors.website" />
                            </div>

                            <div class="md:col-span-2">
                                <InputLabel for="direccion" value="Dirección" class="text-gray-300" />
                                <TextInput
                                    id="direccion"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.direccion"
                                    placeholder="Av. Principal 123, Lima, Perú"
                                />
                                <InputError class="mt-2" :message="form.errors.direccion" />
                            </div>

                            <div class="md:col-span-2">
                                <InputLabel for="notas" value="Notas" class="text-gray-300" />
                                <textarea
                                    id="notas"
                                    v-model="form.notas"
                                    rows="3"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100 rounded-md"
                                    placeholder="Información adicional sobre el cliente..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notas" />
                            </div>
                        </div>
                    </div>

                    <!-- Contacto Principal -->
                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <div class="flex items-center mb-6">
                            <UserIcon class="w-6 h-6 text-green-400 mr-3" />
                            <h3 class="text-lg font-medium text-gray-100">Contacto Principal</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="contacto_nombre" value="Nombre *" class="text-gray-300" />
                                <TextInput
                                    id="contacto_nombre"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.contacto_principal.nombre"
                                    placeholder="Juan"
                                />
                                <InputError class="mt-2" :message="form.errors['contacto_principal.nombre']" />
                            </div>

                            <div>
                                <InputLabel for="contacto_apellidos" value="Apellidos *" class="text-gray-300" />
                                <TextInput
                                    id="contacto_apellidos"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.contacto_principal.apellidos"
                                    placeholder="Pérez García"
                                />
                                <InputError class="mt-2" :message="form.errors['contacto_principal.apellidos']" />
                            </div>

                            <div>
                                <InputLabel for="contacto_puesto" value="Puesto/Cargo" class="text-gray-300" />
                                <TextInput
                                    id="contacto_puesto"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.contacto_principal.puesto"
                                    placeholder="Gerente General"
                                />
                                <InputError class="mt-2" :message="form.errors['contacto_principal.puesto']" />
                            </div>

                            <div>
                                <InputLabel for="contacto_email" value="Email *" class="text-gray-300" />
                                <TextInput
                                    id="contacto_email"
                                    type="email"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.contacto_principal.email"
                                    placeholder="juan.perez@empresa.com"
                                />
                                <InputError class="mt-2" :message="form.errors['contacto_principal.email']" />
                            </div>

                            <div>
                                <InputLabel for="contacto_celular" value="Celular" class="text-gray-300" />
                                <TextInput
                                    id="contacto_celular"
                                    type="text"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-gray-100"
                                    v-model="form.contacto_principal.celular"
                                    placeholder="987654321"
                                />
                                <InputError class="mt-2" :message="form.errors['contacto_principal.celular']" />
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-4">
                        <Link 
                            :href="route('clientes.index')"
                            class="px-6 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700"
                        >
                            {{ form.processing ? 'Creando...' : 'Crear Cliente' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 