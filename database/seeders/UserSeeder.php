<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $itDept = Department::where('code', 'IT')->first();
        $finDept = Department::where('code', 'FIN')->first();

        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'department_id' => $itDept->id,
        ]);

        // Regular User (Requester)
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'department_id' => $itDept->id,
        ]);

        // Approver
        User::create([
            'name' => 'Manager Finance',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'department_id' => $finDept->id,
        ]);

        // PIC
        User::create([
            'name' => 'Procurement Staff',
            'email' => 'procurement@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'department_id' => $finDept->id,
        ]);
    }
}
