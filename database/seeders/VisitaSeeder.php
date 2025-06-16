<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Visita;
use App\Models\User;
use Carbon\Carbon;

class VisitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Cliente::all();
        $users = User::all();

        if ($clientes->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No hay clientes o usuarios disponibles para crear visitas.');
            return;
        }

        $visitas = [
            // Visitas programadas para hoy y próximos días
            [
                'cliente_id' => $clientes[0]->id, // Tech Solutions
                'user_id' => $users[1]->id,
                'titulo' => 'Presentación de Propuesta Técnica',
                'descripcion' => 'Reunión para presentar la propuesta técnica del sistema CRM personalizado.',
                'fecha_programada' => Carbon::today()->addHours(14),
                'estado' => 'programada',
                'tipo' => 'comercial',
                'prioridad' => 'alta',
                'duracion_estimada' => 120,
                'notas' => 'Preparar demo del sistema y documentación técnica.',
                'requiere_seguimiento' => true,
            ],
            [
                'cliente_id' => $clientes[2]->id, // Industrias Manufactureras
                'user_id' => $users[2]->id,
                'titulo' => 'Reunión de Seguimiento ERP',
                'descripcion' => 'Revisión del avance del proyecto de implementación ERP.',
                'fecha_programada' => Carbon::tomorrow()->addHours(10),
                'estado' => 'programada',
                'tipo' => 'seguimiento',
                'prioridad' => 'alta',
                'duracion_estimada' => 90,
                'notas' => 'Revisar cronograma de implementación por módulos.',
                'requiere_seguimiento' => true,
            ],
            
            // Visitas realizadas recientemente
            [
                'cliente_id' => $clientes[4]->id, // Servicios Médicos
                'user_id' => $users[1]->id,
                'titulo' => 'Capacitación Sistema de Gestión',
                'descripcion' => 'Capacitación al personal médico sobre el nuevo sistema de gestión de pacientes.',
                'fecha_programada' => Carbon::yesterday()->addHours(9),
                'fecha_realizada' => Carbon::yesterday()->addHours(9)->addMinutes(105),
                'estado' => 'realizada',
                'tipo' => 'tecnica',
                'prioridad' => 'media',
                'duracion_estimada' => 90,
                'duracion_real' => 105,
                'satisfaccion_cliente' => 5,
                'objetivos_alcanzados' => true,
                'resultado' => 'Capacitación exitosa. El personal se adaptó rápidamente al sistema.',
                'notas' => 'Excelente recepción del sistema por parte del equipo médico.',
                'requiere_seguimiento' => true,
                'fecha_siguiente_contacto' => Carbon::now()->addWeeks(2),
            ],
            [
                'cliente_id' => $clientes[7]->id, // Educación Integral
                'user_id' => $users[3]->id,
                'titulo' => 'Análisis de Requerimientos',
                'descripcion' => 'Reunión para definir los requerimientos del sistema educativo digital.',
                'fecha_programada' => Carbon::now()->subDays(3)->addHours(15),
                'fecha_realizada' => Carbon::now()->subDays(3)->addHours(15)->addMinutes(135),
                'estado' => 'realizada',
                'tipo' => 'comercial',
                'prioridad' => 'alta',
                'duracion_estimada' => 120,
                'duracion_real' => 135,
                'satisfaccion_cliente' => 4,
                'objetivos_alcanzados' => true,
                'resultado' => 'Se definieron los módulos principales: gestión académica, biblioteca virtual y comunicación.',
                'notas' => 'Cliente muy comprometido con el proyecto de transformación digital.',
                'requiere_seguimiento' => true,
                'fecha_siguiente_contacto' => Carbon::now()->addDays(10),
            ],
            
            // Visitas programadas para próxima semana
            [
                'cliente_id' => $clientes[3]->id, // Constructora Nuevo Milenio
                'user_id' => $users[2]->id,
                'titulo' => 'Demostración Software de Gestión de Obras',
                'descripcion' => 'Presentación del módulo de control y seguimiento de proyectos constructivos.',
                'fecha_programada' => Carbon::now()->addDays(5)->addHours(11),
                'estado' => 'programada',
                'tipo' => 'comercial',
                'prioridad' => 'media',
                'duracion_estimada' => 150,
                'notas' => 'Preparar casos de uso específicos para el sector construcción.',
                'requiere_seguimiento' => false,
            ],
            [
                'cliente_id' => $clientes[6]->id, // Restaurante Gourmet
                'user_id' => $users[1]->id,
                'titulo' => 'Implementación Sistema POS',
                'descripcion' => 'Instalación y configuración del sistema de punto de venta.',
                'fecha_programada' => Carbon::now()->addDays(7)->addHours(16),
                'estado' => 'programada',
                'tipo' => 'tecnica',
                'prioridad' => 'media',
                'duracion_estimada' => 180,
                'notas' => 'Coordinar con el equipo de cocina para integración con sistema de comandas.',
                'requiere_seguimiento' => true,
            ],
            
            // Visita cancelada
            [
                'cliente_id' => $clientes[5]->id, // Transportes Rápidos (inactivo)
                'user_id' => $users[2]->id,
                'titulo' => 'Reunión de Reactivación',
                'descripcion' => 'Discusión sobre posible reactivación de servicios.',
                'fecha_programada' => Carbon::now()->subDays(1)->addHours(14),
                'estado' => 'cancelada',
                'tipo' => 'comercial',
                'prioridad' => 'baja',
                'duracion_estimada' => 60,
                'notas' => 'Cliente canceló por problemas financieros internos. Reprogramar para Q2.',
                'requiere_seguimiento' => true,
                'fecha_siguiente_contacto' => Carbon::now()->addMonths(2),
            ],
            
            // Visitas de postventa
            [
                'cliente_id' => $clientes[0]->id, // Tech Solutions
                'user_id' => $users[3]->id,
                'titulo' => 'Soporte Técnico Mensual',
                'descripcion' => 'Revisión mensual del funcionamiento del sistema implementado.',
                'fecha_programada' => Carbon::now()->subWeeks(2)->addHours(10),
                'fecha_realizada' => Carbon::now()->subWeeks(2)->addHours(10)->addMinutes(45),
                'estado' => 'realizada',
                'tipo' => 'postventa',
                'prioridad' => 'media',
                'duracion_estimada' => 60,
                'duracion_real' => 45,
                'satisfaccion_cliente' => 5,
                'objetivos_alcanzados' => true,
                'resultado' => 'Sistema funcionando perfectamente. Se realizaron optimizaciones menores.',
                'notas' => 'Cliente muy satisfecho con el rendimiento del sistema.',
                'requiere_seguimiento' => true,
                'fecha_siguiente_contacto' => Carbon::now()->addMonth(),
            ],
            
            // Visita vencida (no realizada)
            [
                'cliente_id' => $clientes[1]->id, // Comercial Los Andes
                'user_id' => $users[1]->id,
                'titulo' => 'Seguimiento de Cotización',
                'descripcion' => 'Revisión de la propuesta enviada y resolución de dudas.',
                'fecha_programada' => Carbon::now()->subDays(2)->addHours(13),
                'estado' => 'programada',
                'tipo' => 'seguimiento',
                'prioridad' => 'media',
                'duracion_estimada' => 90,
                'notas' => 'Cliente no confirmó asistencia. Contactar para reprogramar.',
                'requiere_seguimiento' => true,
            ]
        ];

        foreach ($visitas as $visitaData) {
            Visita::create($visitaData);
        }
    }
}
