<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Board of Management',
                'code' => 'BOM',
                'description' => 'Board of Management responsible for the overall governance and management of the organization.',
            ],
            [
                'name' => 'Communication Division',
                'code' => 'COMM',
                'description' => 'Handles public relations, communications, publishing, design, events, and external relations.',
            ],
            [
                'name' => 'Museum Division',
                'code' => 'MUS',
                'description' => 'Handles museum projects, museum tours, museum training, language workshops, and school programs.',
            ],
            [
                'name' => 'Operations Division',
                'code' => 'OPS',
                'description' => 'Handles IT support, library services, merchandise, registration, and operational activities.',
            ],
            [
                'name' => 'Community Division',
                'code' => 'COM',
                'description' => 'Handles explorers, heritage tours, study groups, Rumahku, and language speaking sections.',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department
            );
        }
    }
}
