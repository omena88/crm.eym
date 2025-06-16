<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Cliente;
use Inertia\Inertia;

class EmailController extends Controller
{
    /**
     * Página principal de emails
     */
    public function index()
    {
        return Inertia::render('Emails/Index');
    }

    /**
     * Enviar email de bienvenida
     */
    public function enviarBienvenida(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'nombre' => 'required|string|max:255',
        ]);

        try {
            Mail::send('emails.welcome', [
                'nombre' => $request->nombre,
                'fecha' => now()->format('d/m/Y'),
            ], function ($message) use ($request) {
                $message->to($request->email, $request->nombre)
                       ->subject('¡Bienvenido al CRM EYM!');
            });

            return response()->json([
                'success' => true,
                'message' => 'Email de bienvenida enviado exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email de bienvenida: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enviar notificación de pedido
     */
    public function enviarNotificacionPedido(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'nombre' => 'required|string|max:255',
            'numero_pedido' => 'required|string',
            'estado' => 'required|in:nuevo,procesando,completado,cancelado',
            'monto' => 'nullable|numeric',
        ]);

        try {
            $estadoTexto = match($request->estado) {
                'nuevo' => 'Nuevo Pedido Registrado',
                'procesando' => 'Pedido en Proceso',
                'completado' => 'Pedido Completado',
                'cancelado' => 'Pedido Cancelado',
                default => 'Actualización de Pedido',
            };

            Mail::send('emails.order-notification', [
                'nombre' => $request->nombre,
                'numeroPedido' => $request->numero_pedido,
                'estado' => $request->estado,
                'estadoTexto' => $estadoTexto,
                'monto' => $request->monto,
                'fecha' => now()->format('d/m/Y H:i'),
            ], function ($message) use ($request, $estadoTexto) {
                $message->to($request->email, $request->nombre)
                       ->subject("CRM EYM - {$estadoTexto}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Notificación de pedido enviada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de pedido: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la notificación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enviar email de prueba
     */
    public function enviarPrueba(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Mail::raw(
                "Este es un email de prueba del sistema CRM EYM.\n\n" .
                "Si recibes este mensaje, la configuración de email está funcionando correctamente.\n\n" .
                "Fecha: " . now()->format('d/m/Y H:i:s') . "\n" .
                "Sistema: CRM EYM v1.0",
                function ($message) use ($request) {
                    $message->to($request->email)
                           ->subject('Email de Prueba - CRM EYM');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Email de prueba enviado exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email de prueba: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email de prueba: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar configuración de email
     */
    public function verificarConfiguracion()
    {
        try {
            $config = [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'username' => config('mail.mailers.smtp.username'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ];

            // Verificar que los campos obligatorios estén configurados
            $errores = [];
            
            if (empty($config['host'])) {
                $errores[] = 'Host SMTP no configurado';
            }
            
            if (empty($config['username'])) {
                $errores[] = 'Usuario SMTP no configurado';
            }
            
            if (empty($config['from_address'])) {
                $errores[] = 'Dirección de remitente no configurada';
            }

            return response()->json([
                'configuracion' => $config,
                'estado' => empty($errores) ? 'correcto' : 'incompleto',
                'errores' => $errores,
                'mensaje' => empty($errores) 
                    ? 'La configuración de email está completa' 
                    : 'La configuración de email tiene errores',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al verificar la configuración: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enviar email personalizado a cliente
     */
    public function enviarPersonalizado(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'incluir_contacto_principal' => 'boolean',
        ]);

        try {
            $cliente = Cliente::with('contactoPrincipal')->find($request->cliente_id);
            
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $contacto = $cliente->contactoPrincipal;
            
            if (!$contacto || !$contacto->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente no tiene un contacto principal con email',
                ], 400);
            }

            Mail::send('emails.personalizado', [
                'cliente' => $cliente,
                'contacto' => $contacto,
                'mensaje' => $request->mensaje,
                'fecha' => now()->format('d/m/Y'),
            ], function ($message) use ($request, $contacto) {
                $message->to($contacto->email, $contacto->nombre_completo)
                       ->subject($request->asunto);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email enviado exitosamente a ' . $contacto->nombre_completo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email personalizado: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener plantillas de email
     */
    public function plantillas()
    {
        $plantillas = [
            [
                'id' => 'seguimiento',
                'nombre' => 'Seguimiento Comercial',
                'asunto' => 'Seguimiento - {cliente}',
                'contenido' => "Estimado/a {contacto},\n\nEsperamos que se encuentre bien.\n\nNos ponemos en contacto para dar seguimiento a nuestra propuesta comercial presentada el {fecha}.\n\n¿Podríamos coordinar una reunión para resolver cualquier duda?\n\nQuedamos atentos a su respuesta.\n\nSaludos cordiales,\nEquipo CRM EYM",
            ],
            [
                'id' => 'cotizacion',
                'nombre' => 'Envío de Cotización',
                'asunto' => 'Cotización - {cliente}',
                'contenido' => "Estimado/a {contacto},\n\nAdjunto encontrará la cotización solicitada para los servicios de {descripcion}.\n\nLa propuesta tiene vigencia de 30 días y incluye:\n- Descripción detallada del servicio\n- Cronograma de implementación\n- Términos y condiciones\n\nQuedamos a su disposición para cualquier consulta.\n\nSaludos cordiales,\nEquipo CRM EYM",
            ],
            [
                'id' => 'agradecimiento',
                'nombre' => 'Agradecimiento Post-Visita',
                'asunto' => 'Gracias por su tiempo - {cliente}',
                'contenido' => "Estimado/a {contacto},\n\nGracias por recibirnos y por el tiempo dedicado en nuestra reunión del {fecha}.\n\nComo acordamos, enviaremos la propuesta detallada en los próximos días.\n\nMientras tanto, no dude en contactarnos si tiene alguna pregunta.\n\nSaludos cordiales,\nEquipo CRM EYM",
            ],
        ];

        return response()->json($plantillas);
    }
}
