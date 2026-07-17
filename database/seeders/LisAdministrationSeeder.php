<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class LisAdministrationSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::updateOrCreate(
            ['code' => 'PRINCIPAL'],
            [
                'name' => 'Sede principal',
                'legal_name' => 'Clínica de Prueba Master',
                'ruc' => '20123456789',
                'address' => 'Av. Salud y Bienestar 123',
                'phone' => '987654321',
                'email' => 'contacto@hislis.test',
                'is_main' => true,
                'status' => true,
            ]
        );

        $areas = [
            ['hematologia', 'Hematología'],
            ['bioquimica', 'Bioquímica'],
            ['inmunologia', 'Inmunología'],
            ['parasitologia', 'Parasitología'],
            ['microbiologia', 'Microbiología'],
            ['uroanalisis', 'Uroanálisis'],
            ['serologia', 'Serología'],
            ['coagulacion', 'Coagulación'],
            ['hormonas', 'Hormonas'],
            ['biologia-molecular', 'Biología molecular'],
            ['toxicologia', 'Toxicología'],
            ['anatomia-patologica', 'Anatomía patológica'],
        ];

        foreach ($areas as [$slug, $name]) {
            Area::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'status' => true, 'is_medical' => true]
            );
        }

        $admin = User::where('email', 'admin@admin.com')->first();

        if ($admin) {
            $admin->branches()->syncWithoutDetaching([
                $branch->id => ['is_default' => true],
            ]);

            $areaIds = Area::whereIn('slug', ['hematologia', 'bioquimica', 'microbiologia'])->pluck('id');
            foreach ($areaIds as $areaId) {
                $admin->accessibleAreas()->syncWithoutDetaching([
                    $areaId => ['is_default' => false],
                ]);
            }

            $admin->assignRole('super-admin');
        }
    }
}
