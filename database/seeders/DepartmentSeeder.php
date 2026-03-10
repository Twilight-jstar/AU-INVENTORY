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
            'Admissions Office',
            'Alumni\'s Office',
            'Audio Visual Office',
            'Bursar\'s Office',
            'Chairman\'s Office',
            'CLEAR Office',
            'Clinic (Medical and Dental)',
            'Office of Assistant Dean',
            'Data Privacy Officer',
            'Office of the Dean',
            'Office of the Executive Director',
            'Guidance Office',
            'HR/Records Section',
            'Information Technology Center',
            'Institute of Special Studies',
            "LawPhil",
            'Office of the Legal Aid',
            'Office of the General Services',
            'Office of the Student Affairs',
            'Purchasing Department',
            'Phoenix Department',
            'Registrar\'s Office',
        ];

        foreach ($offices as $office) {
            Department::updateOrCreate(
                ['name' => $office],
                ['is_active' => true]
            );
        }
    }
}