<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'IT Department', 'code' => 'IT', 'description' => 'Information Technology'],
            ['name' => 'Finance Department', 'code' => 'FIN', 'description' => 'Finance and Accounting'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human Resources'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Operations'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Marketing'],
            ['name' => 'Sales', 'code' => 'SLS', 'description' => 'Sales'],
            ['name' => 'Procurement', 'code' => 'PRC', 'description' => 'Procurement'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
