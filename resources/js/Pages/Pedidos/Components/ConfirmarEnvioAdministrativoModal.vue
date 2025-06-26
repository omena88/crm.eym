<script setup>
import Modal from '@/Components/Modal.vue';
import { EnvelopeIcon, XMarkIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

defineProps({
    show: Boolean,
    pedido: Object,
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
        <div class="p-6 bg-gray-900 text-white rounded-lg shadow-xl border border-gray-800">
            <!-- Header del Modal -->
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <EnvelopeIcon class="w-6 h-6 mr-3 text-blue-400" />
                    Envío a Área Administrativa
                </h2>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-300">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Simulación del correo -->
            <div class="bg-white text-gray-900 rounded-lg p-6 mb-6 shadow-inner">
                <!-- Header del correo -->
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">Para:</span>
                            <span class="ml-2">administracion@empresa.com</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">De:</span>
                            <span class="ml-2">ventas@empresa.com</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium text-gray-600">Asunto:</span>
                            <span class="ml-2">Nuevo Pedido Aprobado - {{ pedido.numero }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cuerpo del correo -->
                <div class="space-y-4">
                    <div class="text-gray-800">
                        <p class="mb-4">Estimado equipo administrativo,</p>
                        <p class="mb-4">
                            Se ha generado un nuevo pedido que requiere procesamiento. A continuación los detalles:
                        </p>
                    </div>

                    <!-- Información del pedido -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center mb-3">
                            <BuildingOfficeIcon class="w-5 h-5 text-blue-600 mr-2" />
                            <h3 class="font-bold text-gray-900">Información del Pedido</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-600">Número:</span>
                                <span class="ml-2 text-gray-900">{{ pedido.numero }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Fecha:</span>
                                <span class="ml-2 text-gray-900">{{ pedido.fecha_creacion }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Cliente:</span>
                                <span class="ml-2 text-gray-900">{{ pedido.cliente.razon_social }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Vendedor:</span>
                                <span class="ml-2 text-gray-900">{{ pedido.vendedor.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de productos -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="font-bold text-gray-900 mb-3">Productos Solicitados</h3>
                        
                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Producto</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 uppercase">Cantidad</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 uppercase">Precio Unit.</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="producto in pedido.productos" :key="producto.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ producto.nombre }}</div>
                                                <div class="text-sm text-gray-600">{{ producto.descripcion }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-900">{{ producto.cantidad }}</td>
                                        <td class="px-4 py-3 text-right text-gray-900">{{ formatCurrency(producto.precio_base) }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900">
                                            {{ formatCurrency(producto.precio_base * producto.cantidad) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-100">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-900">TOTAL:</td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900 text-lg">
                                            {{ formatCurrency(pedido.total) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Instrucciones -->
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <h3 class="font-bold text-blue-900 mb-2">Instrucciones para el procesamiento:</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Verificar disponibilidad de productos en inventario</li>
                            <li>• Coordinar con el área de logística para la entrega</li>
                            <li>• Generar factura correspondiente</li>
                            <li>• Notificar al vendedor una vez procesado</li>
                        </ul>
                    </div>

                    <!-- Observaciones si las hay -->
                    <div v-if="pedido.observaciones" class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <h3 class="font-bold text-yellow-900 mb-2">Observaciones:</h3>
                        <p class="text-sm text-yellow-800">{{ pedido.observaciones }}</p>
                    </div>

                    <!-- Firma -->
                    <div class="text-gray-800 pt-4 border-t border-gray-200">
                        <p class="mb-2">Saludos cordiales,</p>
                        <p class="font-medium">Equipo Comercial</p>
                        <p class="text-sm text-gray-600">Sistema de Gestión CRM</p>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4">
                <button 
                    @click="$emit('close')"
                    class="px-4 py-2 text-gray-300 hover:text-white transition-colors"
                >
                    Cancelar
                </button>
                <button 
                    @click="$emit('confirm')"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center"
                >
                    <EnvelopeIcon class="w-4 h-4 mr-2" />
                    Enviar a Administración
                </button>
            </div>
        </div>
    </Modal>
</template> 