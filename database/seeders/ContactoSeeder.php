<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Contacto;

class ContactoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Cliente::all();

        $contactosData = [
            // Tech Solutions S.A.C.
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Mendoza Rivera',
                'puesto' => 'Gerente General',
                'telefono' => '01-234-5678',
                'celular' => '987-654-321',
                'email' => 'carlos.mendoza@techsolutions.com.pe',
                'es_contacto_principal' => true,
                'notas' => 'Contacto principal para decisiones técnicas y comerciales.'
            ],
            [
                'nombre' => 'Ana',
                'apellidos' => 'García López',
                'puesto' => 'Jefa de Sistemas',
                'telefono' => '01-234-5679',
                'celular' => '987-654-322',
                'email' => 'ana.garcia@techsolutions.com.pe',
                'es_contacto_principal' => false,
                'notas' => 'Responsable técnica del proyecto de implementación.'
            ],
            
            // Comercial Los Andes E.I.R.L.
            [
                'nombre' => 'Roberto',
                'apellidos' => 'Quispe Mamani',
                'puesto' => 'Propietario',
                'telefono' => '01-345-6789',
                'celular' => '976-543-210',
                'email' => 'roberto.quispe@gmail.com',
                'es_contacto_principal' => true,
                'notas' => 'Dueño del negocio familiar. Toma todas las decisiones.'
            ],
            
            // Industrias Manufactureras del Perú S.A.
            [
                'nombre' => 'María',
                'apellidos' => 'Fernández Castillo',
                'puesto' => 'Directora de Operaciones',
                'telefono' => '01-456-7890',
                'celular' => '965-432-109',
                'email' => 'maria.fernandez@industriasmp.com',
                'es_contacto_principal' => true,
                'notas' => 'Responsable de la transformación digital de la empresa.'
            ],
            [
                'nombre' => 'Luis',
                'apellidos' => 'Torres Vega',
                'puesto' => 'Jefe de IT',
                'telefono' => '01-456-7891',
                'celular' => '965-432-108',
                'email' => 'luis.torres@industriasmp.com',
                'es_contacto_principal' => false,
                'notas' => 'Encargado de la parte técnica del proyecto ERP.'
            ],
            
            // Constructora Nuevo Milenio S.A.C.
            [
                'nombre' => 'Jorge',
                'apellidos' => 'Ramírez Silva',
                'puesto' => 'Gerente de Proyectos',
                'telefono' => '01-567-8901',
                'celular' => '954-321-098',
                'email' => 'jorge.ramirez@nuevomilenio.pe',
                'es_contacto_principal' => true,
                'notas' => 'Interesado en software para gestión de múltiples obras simultáneas.'
            ],
            
            // Servicios Médicos Especializado S.A.
            [
                'nombre' => 'Dra. Patricia',
                'apellidos' => 'Morales Herrera',
                'puesto' => 'Directora Médica',
                'telefono' => '01-678-9012',
                'celular' => '943-210-987',
                'email' => 'patricia.morales@serviciosmedicos.com.pe',
                'es_contacto_principal' => true,
                'notas' => 'Médica especialista preocupada por mejorar la atención al paciente.'
            ],
            [
                'nombre' => 'Miguel',
                'apellidos' => 'Vargas Ochoa',
                'puesto' => 'Administrador',
                'telefono' => '01-678-9013',
                'celular' => '943-210-986',
                'email' => 'miguel.vargas@serviciosmedicos.com.pe',
                'es_contacto_principal' => false,
                'notas' => 'Maneja la parte administrativa y financiera de la clínica.'
            ],
            
            // Transportes Rápidos del Norte S.R.L.
            [
                'nombre' => 'Pedro',
                'apellidos' => 'Huamán Ríos',
                'puesto' => 'Gerente',
                'telefono' => '01-789-0123',
                'celular' => '932-109-876',
                'email' => 'pedro.huaman@transportesnorte.com',
                'es_contacto_principal' => true,
                'notas' => 'Cliente inactivo. Pendiente resolución de deudas.'
            ],
            
            // Restaurante Gourmet La Mesa S.A.C.
            [
                'nombre' => 'Chef Isabella',
                'apellidos' => 'Rossi Martínez',
                'puesto' => 'Gerente General',
                'telefono' => '01-890-1234',
                'celular' => '921-098-765',
                'email' => 'isabella.rossi@lamesa.com.pe',
                'es_contacto_principal' => true,
                'notas' => 'Chef propietaria con visión de expansión nacional.'
            ],
            [
                'nombre' => 'Franco',
                'apellidos' => 'Delgado Peña',
                'puesto' => 'Administrador',
                'telefono' => '01-890-1235',
                'celular' => '921-098-764',
                'email' => 'franco.delgado@lamesa.com.pe',
                'es_contacto_principal' => false,
                'notas' => 'Maneja las finanzas y operaciones del restaurante.'
            ],
            
            // Educación Integral del Futuro S.A.
            [
                'nombre' => 'Dr. Alejandro',
                'apellidos' => 'Salinas Mendoza',
                'puesto' => 'Director Académico',
                'telefono' => '01-901-2345',
                'celular' => '910-987-654',
                'email' => 'alejandro.salinas@educacionfuturo.edu.pe',
                'es_contacto_principal' => true,
                'notas' => 'Visionario de la educación digital. Impulsa la transformación tecnológica.'
            ],
            [
                'nombre' => 'Carmen',
                'apellidos' => 'Jiménez Torres',
                'puesto' => 'Coordinadora de Sistemas',
                'telefono' => '01-901-2346',
                'celular' => '910-987-653',
                'email' => 'carmen.jimenez@educacionfuturo.edu.pe',
                'es_contacto_principal' => false,
                'notas' => 'Encargada de coordinar la implementación tecnológica en todas las sedes.'
            ]
        ];

        $clienteIndex = 0;
        $contactosPerClient = [2, 1, 2, 1, 2, 1, 2, 2]; // Cantidad de contactos por cliente

        foreach ($contactosPerClient as $index => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                $contactoData = $contactosData[$clienteIndex];
                $contactoData['cliente_id'] = $clientes[$index]->id;
                
                Contacto::create($contactoData);
                $clienteIndex++;
            }
        }
    }
}
