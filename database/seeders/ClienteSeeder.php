<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Contacto;
use Carbon\Carbon;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientesData = [
            [
                'ruc' => '20123456789',
                'razon_social' => 'Constructora Lima Norte S.A.C.',
                'sector' => 'Sector 01',
                'estado' => 'Aprobado',
                'telefono' => '01-4567890',
                'website' => 'https://www.constructoralima.com.pe',
                'direccion' => 'Av. Túpac Amaru 1245, Los Olivos, Lima',
                'contactos' => [
                    [
                        'nombre' => 'Carlos',
                        'apellidos' => 'Rodriguez Vargas',
                        'puesto' => 'Gerente General',
                        'email' => 'carlos.rodriguez@constructoralima.com.pe',
                        'celular' => '987654321',
                        'es_contacto_principal' => true,
                    ],
                    [
                        'nombre' => 'Ana',
                        'apellidos' => 'Martinez Silva',
                        'puesto' => 'Jefa de Proyectos',
                        'email' => 'ana.martinez@constructoralima.com.pe',
                        'celular' => '976543210',
                        'es_contacto_principal' => false,
                    ],
                ],
            ],
            [
                'ruc' => '20234567890',
                'razon_social' => 'Textiles San Martín E.I.R.L.',
                'sector' => 'Sector 02',
                'estado' => 'Visitado',
                'telefono' => '01-3456789',
                'website' => 'https://www.textiles-sanmartin.com.pe',
                'direccion' => 'Jr. Gamarra 456, La Victoria, Lima',
                'contactos' => [
                    [
                        'nombre' => 'María',
                        'apellidos' => 'Gonzales Peña',
                        'puesto' => 'Gerente de Ventas',
                        'email' => 'maria.gonzales@textiles-sanmartin.com.pe',
                        'celular' => '965432109',
                        'es_contacto_principal' => true,
                    ],
                ],
            ],
            [
                'ruc' => '20345678901',
                'razon_social' => 'Tecnología Digital Perú S.A.',
                'sector' => 'Sector 03',
                'estado' => 'Por cotizar',
                'telefono' => '01-2345678',
                'website' => 'https://www.tecdigitalperu.com',
                'direccion' => 'Av. Javier Prado Este 2850, San Isidro, Lima',
                'contactos' => [
                    [
                        'nombre' => 'Luis',
                        'apellidos' => 'Herrera Campos',
                        'puesto' => 'Director de TI',
                        'email' => 'luis.herrera@tecdigitalperu.com',
                        'celular' => '954321098',
                        'es_contacto_principal' => true,
                    ],
                    [
                        'nombre' => 'Sandra',
                        'apellidos' => 'Torres Mendoza',
                        'puesto' => 'Coordinadora de Proyectos',
                        'email' => 'sandra.torres@tecdigitalperu.com',
                        'celular' => '943210987',
                        'es_contacto_principal' => false,
                    ],
                ],
            ],
            [
                'ruc' => '20456789012',
                'razon_social' => 'Minera Huancayo S.A.C.',
                'sector' => 'Sector 04',
                'estado' => 'Cotizado',
                'telefono' => '064-234567',
                'website' => 'https://www.minerahuancayo.com.pe',
                'direccion' => 'Av. Ferrocarril 123, Huancayo, Junín',
                'contactos' => [
                    [
                        'nombre' => 'Roberto',
                        'apellidos' => 'Quispe Mamani',
                        'puesto' => 'Jefe de Operaciones',
                        'email' => 'roberto.quispe@minerahuancayo.com.pe',
                        'celular' => '932109876',
                        'es_contacto_principal' => true,
                    ],
                ],
            ],
            [
                'ruc' => '20567890123',
                'razon_social' => 'Distribuidora El Comerciante S.R.L.',
                'sector' => 'Sector 05',
                'estado' => 'Pendiente',
                'telefono' => '01-5678901',
                'website' => 'https://www.elcomerciante.com.pe',
                'direccion' => 'Av. Colonial 789, Callao',
                'contactos' => [
                    [
                        'nombre' => 'Patricia',
                        'apellidos' => 'Lopez Rivera',
                        'puesto' => 'Gerente Comercial',
                        'email' => 'patricia.lopez@elcomerciante.com.pe',
                        'celular' => '921098765',
                        'es_contacto_principal' => true,
                    ],
                    [
                        'nombre' => 'Jorge',
                        'apellidos' => 'Vargas Castro',
                        'puesto' => 'Supervisor de Almacén',
                        'email' => 'jorge.vargas@elcomerciante.com.pe',
                        'celular' => '910987654',
                        'es_contacto_principal' => false,
                    ],
                ],
            ],
            [
                'ruc' => '20678901234',
                'razon_social' => 'Servicios Gastronómicos Arequipa E.I.R.L.',
                'sector' => 'Sector 06',
                'estado' => 'Visitado',
                'telefono' => '054-345678',
                'website' => 'https://www.gastroarequipa.com.pe',
                'direccion' => 'Calle Mercaderes 234, Arequipa',
                'contactos' => [
                    [
                        'nombre' => 'Carmen',
                        'apellidos' => 'Flores Huaman',
                        'puesto' => 'Chef Ejecutiva',
                        'email' => 'carmen.flores@gastroarequipa.com.pe',
                        'celular' => '909876543',
                        'es_contacto_principal' => true,
                    ],
                ],
            ],
            [
                'ruc' => '20789012345',
                'razon_social' => 'Transporte Andino Express S.A.',
                'sector' => 'Sector 07',
                'estado' => 'Aprobado',
                'telefono' => '084-456789',
                'website' => 'https://www.andinoexpress.com.pe',
                'direccion' => 'Av. Cultura 567, Cusco',
                'contactos' => [
                    [
                        'nombre' => 'Miguel',
                        'apellidos' => 'Condori Auccasi',
                        'puesto' => 'Director de Operaciones',
                        'email' => 'miguel.condori@andinoexpress.com.pe',
                        'celular' => '998765432',
                        'es_contacto_principal' => true,
                    ],
                    [
                        'nombre' => 'Elena',
                        'apellidos' => 'Sanchez Ramos',
                        'puesto' => 'Coordinadora de Flota',
                        'email' => 'elena.sanchez@andinoexpress.com.pe',
                        'celular' => '987654321',
                        'es_contacto_principal' => false,
                    ],
                ],
            ],
            [
                'ruc' => '20890123456',
                'razon_social' => 'Farmacia y Botica Central S.A.C.',
                'sector' => 'Sector 08',
                'estado' => 'Rechazado',
                'telefono' => '074-567890',
                'website' => 'https://www.farmaciacentral.com.pe',
                'direccion' => 'Jr. Lima 890, Chiclayo, Lambayeque',
                'contactos' => [
                    [
                        'nombre' => 'Fernando',
                        'apellidos' => 'Reyes Morales',
                        'puesto' => 'Químico Farmacéutico',
                        'email' => 'fernando.reyes@farmaciacentral.com.pe',
                        'celular' => '976543210',
                        'es_contacto_principal' => true,
                    ],
                ],
            ],
            [
                'ruc' => '20901234567',
                'razon_social' => 'Educación Superior Innovadora S.A.C.',
                'sector' => 'Sector 09',
                'estado' => 'Por cotizar',
                'telefono' => '076-678901',
                'website' => 'https://www.eduinnovadora.edu.pe',
                'direccion' => 'Av. Universitaria 1234, Cajamarca',
                'contactos' => [
                    [
                        'nombre' => 'Rosa',
                        'apellidos' => 'Chavez Villanueva',
                        'puesto' => 'Directora Académica',
                        'email' => 'rosa.chavez@eduinnovadora.edu.pe',
                        'celular' => '965432109',
                        'es_contacto_principal' => true,
                    ],
                    [
                        'nombre' => 'Alberto',
                        'apellidos' => 'Mendoza Silva',
                        'puesto' => 'Coordinador de Sistemas',
                        'email' => 'alberto.mendoza@eduinnovadora.edu.pe',
                        'celular' => '954321098',
                        'es_contacto_principal' => false,
                    ],
                ],
            ],
            [
                'ruc' => '21012345678',
                'razon_social' => 'Agricultura Sostenible del Norte E.I.R.L.',
                'sector' => 'Sector 10',
                'estado' => 'Cotizado',
                'telefono' => '073-789012',
                'website' => 'https://www.agrisostenible.com.pe',
                'direccion' => 'Carretera Panamericana Norte Km 972, Piura',
                'contactos' => [
                    [
                        'nombre' => 'Javier',
                        'apellidos' => 'Paredes Ojeda',
                        'puesto' => 'Ingeniero Agrónomo',
                        'email' => 'javier.paredes@agrisostenible.com.pe',
                        'celular' => '943210987',
                        'es_contacto_principal' => true,
                    ],
                ],
            ],
        ];

        foreach ($clientesData as $clienteData) {
            $contactos = $clienteData['contactos'];
            unset($clienteData['contactos']);

            $cliente = Cliente::create($clienteData);

            foreach ($contactos as $contactoData) {
                $cliente->contactos()->create($contactoData);
            }
        }
    }
}
