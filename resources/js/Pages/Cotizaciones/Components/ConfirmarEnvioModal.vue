<script setup>
import Modal from '@/Components/Modal.vue';
import { PaperAirplaneIcon, XMarkIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';

defineProps({
    show: Boolean,
    cliente: Object,
    cotizacion: Object,
});

const emit = defineEmits(['close', 'confirm']);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP'
    }).format(value);
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')" max-width="4xl">
        <div class="bg-gray-900 text-white rounded-lg shadow-xl border border-gray-800 max-h-[90vh] overflow-y-auto">
            <!-- Header del correo -->
            <div class="p-6 border-b border-gray-800">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <EnvelopeIcon class="w-6 h-6 mr-3 text-blue-400" />
                    Vista previa del correo - Cotización
                </h2>
                
                <!-- Campos de correo -->
                <div class="space-y-3 text-sm">
                    <div class="flex">
                        <span class="w-16 text-gray-400 font-medium">Para:</span>
                        <span class="text-white">{{ cliente?.razon_social || 'Cliente no disponible' }} &lt;contacto@cliente.com&gt;</span>
                    </div>
                    <div class="flex">
                        <span class="w-16 text-gray-400 font-medium">De:</span>
                        <span class="text-white">EYM Comercial &lt;comercial@eym.com&gt;</span>
                    </div>
                    <div class="flex">
                        <span class="w-16 text-gray-400 font-medium">Asunto:</span>
                        <span class="text-white">Cotización #{{ cotizacion?.id || '123' }} - {{ cliente?.razon_social || 'Cliente' }}</span>
                    </div>
                </div>
            </div>

            <!-- Cuerpo del correo -->
            <div class="p-6 bg-white text-gray-900 mx-6 my-4 rounded-lg border">
                <div class="max-w-none">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Estimado cliente,</h3>
                    
                    <p class="mb-4 text-gray-700">
                        Nos complace enviarle la cotización solicitada para los productos de su interés.
                    </p>

                    <!-- Tabla de productos -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Detalle de productos:</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 px-4 py-2 text-left">Producto</th>
                                        <th class="border border-gray-300 px-4 py-2 text-center">Cantidad</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Precio Unit.</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="producto in cotizacion?.productos" :key="producto.id">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <div class="font-medium">{{ producto.nombre }}</div>
                                            <div class="text-sm text-gray-600">{{ producto.descripcion }}</div>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ producto.cantidad }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(producto.precio_base) }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ formatCurrency(producto.precio_base * producto.cantidad) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="3" class="border border-gray-300 px-4 py-2 text-right font-bold">Total:</td>
                                        <td class="border border-gray-300 px-4 py-2 text-right font-bold text-lg">{{ formatCurrency(cotizacion?.total || 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-3 text-gray-700">
                        <p><strong>Condiciones comerciales:</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Precios válidos por 30 días</li>
                            <li>Precios no incluyen IVA</li>
                            <li>Tiempo de entrega: 15 días hábiles</li>
                            <li>Forma de pago: 30% anticipo, saldo contra entrega</li>
                        </ul>
                    </div>

                    <p class="mt-6 text-gray-700">
                        Quedamos atentos a sus comentarios y esperamos poder concretar este negocio.
                    </p>

                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <p class="font-medium text-gray-900">Saludos cordiales,</p>
                        <p class="text-gray-700">Equipo Comercial EYM</p>
                        <p class="text-sm text-gray-600">comercial@eym.com | +56 9 1234 5678</p>
                    </div>
                </div>
            </div>

            <!-- Footer con acciones -->
            <div class="p-6 border-t border-gray-800">
                <p class="text-sm text-gray-400 mb-4">
                    Esta es una simulación. Al confirmar, la cotización se marcará como <span class="font-semibold text-green-400">Enviada</span>.
                </p>
                <div class="flex justify-end space-x-4">
                    <button @click="$emit('close')" class="px-4 py-2 rounded-md text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 transition-colors">
                        <XMarkIcon class="w-4 h-4 inline-block mr-1" />
                        Cancelar
                    </button>
                    <button @click="$emit('confirm')" class="px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                        <PaperAirplaneIcon class="w-4 h-4 inline-block mr-1" />
                        Enviar Cotización
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template> 