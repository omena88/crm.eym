<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { ArrowLeftIcon, PlusIcon, TrashIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    pedido: Object,
    clientes: Array,
    productos: Array,
});

const form = useForm({
    cliente_id: props.pedido?.cliente_id || '',
    productos: props.pedido?.productos || [],
    observaciones: props.pedido?.observaciones || '',
});

const mostrarSelectorProductos = ref(false);
const busquedaProducto = ref('');
const clienteSeleccionado = ref(null);

const productosFiltrados = computed(() => {
    if (!busquedaProducto.value) return props.productos;
    return props.productos.filter(producto => 
        producto.nombre.toLowerCase().includes(busquedaProducto.value.toLowerCase()) ||
        producto.descripcion.toLowerCase().includes(busquedaProducto.value.toLowerCase())
    );
});

const total = computed(() => {
    return form.productos.reduce((sum, producto) => {
        return sum + (producto.precio_base * producto.cantidad);
    }, 0);
});

const agregarProducto = (producto) => {
    const existente = form.productos.find(p => p.id === producto.id);
    if (existente) {
        existente.cantidad += 1;
    } else {
        form.productos.push({
            id: producto.id,
            nombre: producto.nombre,
            descripcion: producto.descripcion,
            precio_base: producto.precio_base,
            cantidad: 1,
        });
    }
    mostrarSelectorProductos.value = false;
    busquedaProducto.value = '';
};

const eliminarProducto = (index) => {
    form.productos.splice(index, 1);
};

const actualizarCantidad = (index, cantidad) => {
    if (cantidad <= 0) {
        eliminarProducto(index);
    } else {
        form.productos[index].cantidad = cantidad;
    }
};

const submit = () => {
    form.put(route('pedidos.update', props.pedido.id));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP'
    }).format(value);
};

const buscarCliente = () => {
    if (form.cliente_id) {
        clienteSeleccionado.value = props.clientes.find(c => c.id == form.cliente_id);
    }
};

onMounted(() => {
    buscarCliente();
});
</script>

<template>
    <Head :title="`Editar Pedido ${pedido.numero}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('pedidos.index')" class="text-gray-400 hover:text-white">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-100">
                        Editar Pedido {{ pedido.numero }}
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Modificar información del pedido existente
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="bg-yellow-900/50 border border-yellow-700 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-yellow-100 mb-2">Editando Pedido</h3>
                    <p class="text-yellow-200">
                        Estás editando el pedido <span class="font-medium">{{ pedido.numero }}</span>. 
                        Los cambios se guardarán al hacer clic en "Actualizar Pedido".
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <div class="lg:col-span-2">
                            <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-white mb-4">Información del Pedido</h3>
                                
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Cliente
                                    </label>
                                    <select 
                                        v-model="form.cliente_id" 
                                        @change="buscarCliente"
                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Seleccionar cliente...</option>
                                        <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                            {{ cliente.razon_social }}
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <label class="block text-sm font-medium text-gray-300">
                                            Productos
                                        </label>
                                        <button 
                                            type="button"
                                            @click="mostrarSelectorProductos = !mostrarSelectorProductos"
                                            class="flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                                        >
                                            <PlusIcon class="w-4 h-4 mr-2" />
                                            Agregar Producto
                                        </button>
                                    </div>

                                    <div v-if="mostrarSelectorProductos" class="mb-4 p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                                        <div class="flex items-center mb-3">
                                            <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 mr-2" />
                                            <input 
                                                v-model="busquedaProducto"
                                                type="text"
                                                placeholder="Buscar productos..."
                                                class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                        </div>
                                        <div class="max-h-48 overflow-y-auto space-y-2">
                                            <div 
                                                v-for="producto in productosFiltrados" 
                                                :key="producto.id"
                                                @click="agregarProducto(producto)"
                                                class="p-3 bg-gray-800 hover:bg-gray-700 rounded-lg cursor-pointer transition-colors"
                                            >
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-medium text-white">{{ producto.nombre }}</p>
                                                        <p class="text-sm text-gray-400">{{ producto.descripcion }}</p>
                                                    </div>
                                                    <p class="text-white font-medium">{{ formatCurrency(producto.precio_base) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="form.productos.length > 0" class="space-y-3">
                                        <div 
                                            v-for="(producto, index) in form.productos" 
                                            :key="producto.id"
                                            class="p-4 bg-gray-800/50 rounded-lg border border-gray-700"
                                        >
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <p class="font-medium text-white">{{ producto.nombre }}</p>
                                                    <p class="text-sm text-gray-400">{{ producto.descripcion }}</p>
                                                </div>
                                                <div class="flex items-center space-x-3 ml-4">
                                                    <div class="flex items-center space-x-2">
                                                        <label class="text-sm text-gray-400">Cantidad:</label>
                                                        <input 
                                                            :value="producto.cantidad"
                                                            @input="actualizarCantidad(index, parseInt($event.target.value))"
                                                            type="number" 
                                                            min="1"
                                                            class="w-20 px-2 py-1 bg-gray-800 border border-gray-700 rounded text-white text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                        >
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-white font-medium">{{ formatCurrency(producto.precio_base * producto.cantidad) }}</p>
                                                        <p class="text-sm text-gray-400">{{ formatCurrency(producto.precio_base) }} c/u</p>
                                                    </div>
                                                    <button 
                                                        type="button"
                                                        @click="eliminarProducto(index)"
                                                        class="text-red-400 hover:text-red-300 p-1"
                                                    >
                                                        <TrashIcon class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else class="text-center py-8 text-gray-400">
                                        <p>No hay productos seleccionados</p>
                                        <p class="text-sm">Haz clic en "Agregar Producto" para comenzar</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Observaciones
                                    </label>
                                    <textarea 
                                        v-model="form.observaciones"
                                        rows="3"
                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Observaciones adicionales para el pedido..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-1">
                            <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 sticky top-8">
                                <h3 class="text-lg font-semibold text-white mb-4">Resumen del Pedido</h3>
                                
                                <div class="space-y-4">
                                    <div class="p-3 bg-gray-800/50 rounded-lg">
                                        <p class="text-sm text-gray-400">Número</p>
                                        <p class="text-white font-medium">{{ pedido.numero }}</p>
                                    </div>
                                    
                                    <div v-if="clienteSeleccionado" class="p-3 bg-gray-800/50 rounded-lg">
                                        <p class="text-sm text-gray-400">Cliente</p>
                                        <p class="text-white font-medium">{{ clienteSeleccionado.razon_social }}</p>
                                    </div>

                                    <div class="p-3 bg-gray-800/50 rounded-lg">
                                        <p class="text-sm text-gray-400">Productos</p>
                                        <p class="text-white font-medium">{{ form.productos.length }} productos</p>
                                    </div>

                                    <div class="p-3 bg-blue-900/50 rounded-lg border border-blue-700">
                                        <p class="text-sm text-blue-200">Total</p>
                                        <p class="text-2xl font-bold text-white">{{ formatCurrency(total) }}</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <button 
                                        type="submit"
                                        :disabled="form.processing || form.productos.length === 0 || !form.cliente_id"
                                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors"
                                    >
                                        {{ form.processing ? 'Actualizando...' : 'Actualizar Pedido' }}
                                    </button>
                                    
                                    <Link 
                                        :href="route('pedidos.index')"
                                        class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors text-center block"
                                    >
                                        Cancelar
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 