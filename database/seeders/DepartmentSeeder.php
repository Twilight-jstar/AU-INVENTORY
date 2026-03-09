<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            // Original List
            'Accounting Office',
            'Auditing Office',
            'Admissions Office 1',
            'Admissions Office 2',
            'Alumni\'s Office',
            'Audio Visual Office',
            'Bursar\'s Office Line 1',
            'Bursar\'s Office Line 2',
            'CCTV',
            'Chairman\'s Office',
            'CLEAR Office',
            'Clinic (Medical)',
            'Clinic (Dental)',
            'College Secretary (Sir Ronald)',
            'Data Privacy Officer - Atty. Mau Patajo',
            'Dean\'s Office',
            'Donada Gate',
            'Executive Director - Atty. Gabriel P. Dela Peña',
            'Guidance Office',
            'HR/Records Section',
            'Information Technology Center',
            'ISS',
            'Operator (ISSA)',
            'Operator (EMY)',
            'Operator (GRACE)',
            'Office of the Legal Aid',
            'Office of the General Services',
            'Office of the Student Affairs',
            'Purchasing Department',
            'Phoenix Department',
            'Registrar\'s Office',
            'Taft Gate',
        ];

        foreach ($offices as $office) {
            Department::updateOrCreate(
                ['name' => $office],
                ['is_active' => true]
            );
        }
    }
}