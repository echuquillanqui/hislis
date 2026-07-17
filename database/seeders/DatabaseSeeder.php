<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
// Asegúrate de que estos modelos existan en tu carpeta App\Models
use App\Models\{User, Area, Setting, Patient, Template, Triage, Voucher, OrderItem, Service, LabExam};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Desactivar llaves foráneas para limpieza segura
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar tablas para evitar errores de duplicidad
        Setting::truncate();
        Area::truncate();
        User::truncate();
        Patient::truncate();
        Template::truncate();

        // 1. CONFIGURACIÓN DEL HOSPITAL
        Setting::create([
            'hospital_name' => 'Clínica de Prueba Master',
            'ruc_nit' => '20123456789',
            'address' => 'Av. Salud y Bienestar 123',
            'phone' => '987654321',
            'currency' => 'PEN'
        ]);

        // 2. ÁREAS MÉDICAS
        // El monitor médico suele filtrar por is_medical = true
        $areaGine = Area::create(['name' => 'Ginecología', 'slug' => 'gine', 'is_medical' => true]);
        $areaLab  = Area::create(['name' => 'Laboratorio', 'slug' => 'lab', 'is_medical' => true]);
        $areaTri  = Area::create(['name' => 'Triaje', 'slug' => 'triaje', 'is_medical' => true]);

        // 3. ROLES Y PERMISOS (Spatie)
        $roleAdmin = Role::firstOrCreate(['name' => 'SUPERADMIN', 'guard_name' => 'web']);
        $permissions = ['HIS_ver_pacientes', 'HIS_atender', 'CONFIG_all'];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $roleAdmin->givePermissionTo(Permission::all());

        // 4. USUARIO ADMINISTRADOR
        $admin = User::create([
            'name' => 'Admin Sistema',
            'username' => 'admin',
            'dni' => '12345678',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'area_id' => $areaGine->id,
            'status' => true
        ]);
        $admin->assignRole($roleAdmin);

        // 5. PLANTILLAS CLÍNICAS
        Template::create([
            'name' => 'Atención General Especializada',
            'schema' => json_encode([
                ['name' => 'motivo', 'label' => 'Motivo de Consulta', 'type' => 'text'],
                ['name' => 'anamnesis', 'label' => 'Anamnesis', 'type' => 'textarea'],
                ['name' => 'diagnostico', 'label' => 'Diagnóstico', 'type' => 'text']
            ])
        ]);

        // 6. SERVICIOS Y EXÁMENES (Maestros)
        // Nota: Estos modelos no estaban en tus archivos pero son necesarios para order_items
        $servicio = Service::create([
            'area_id' => $areaGine->id,
            'name' => 'Consulta Ginecología',
            'price' => 50.00,
            'status' => 1
        ]);

        // 7. GENERACIÓN DE DATA DE PRUEBA (5 Pacientes con Triaje y Orden)
        for ($i = 1; $i <= 5; $i++) {
            // A. Crear Paciente
            $paciente = Patient::create([
                'dni' => '7000000' . $i,
                'first_name' => 'Paciente',
                'last_name' => 'Ejemplo ' . $i,
                'phone' => '99988877' . $i,
                'birth_date' => '1990-01-01',
                'gender' => 'F'
            ]);

            // B. Generar Voucher (Orden de Venta)
            $voucher = Voucher::create([
                'patient_id' => $paciente->id,
                'user_id' => $admin->id,
                'type' => 'TICKET',
                'series' => 'TK01',
                'number' => $i,
                'total' => 50.00,
                'status' => 'paid' // "paid" suele ser el trigger para el monitor
            ]);

            // C. Generar Triaje (Iniciado con valores en 0)
            Triage::create([
                'patient_id' => $paciente->id,
                'user_id' => $admin->id,
                'temp' => '0', 'bp' => '0/0', 'hr' => '0', 'rr' => '0', 
                'weight' => '0', 'height' => '0', 'bmi' => '0'
            ]);

            // D. Generar Item de Atención
            OrderItem::create([
                'voucher_id' => $voucher->id,
                'itemable_id' => $servicio->id,
                'itemable_type' => Service::class, // Morph polimórfico
                'price' => 50.00,
                'status' => 'pending' // Esto hace que aparezca en el monitor
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call([
            LisRolesAndPermissionsSeeder::class,
            LisCatalogSeeder::class,
            LisAdministrationSeeder::class,
            LisPatientCustomerSeeder::class,
        ]);
    }
}