<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Reset permission cache sebelum assign roles
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $itDept  = Department::where('code', 'IT')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        /**
         * Setiap entry punya:
         *   - data user biasa (kolom users table)
         *   - 'spatie_role' => nama Spatie role untuk filament-shield
         */
        $users = [
            // ─── Super Admin ────────────────────────────────────────────
            [
                'name'          => 'Super Admin',
                'email'         => 'jr.pikong@gmail.com',
                'password'      => Hash::make('password'),
                'role'          => 'super_admin',
                'is_active'     => true,
                'department_id' => $itDept?->id,
                'position'      => 'Director',
                'spatie_role'   => 'super_admin',
            ],
            // ─── Admin ──────────────────────────────────────────────────
            [
                'name'          => 'Admin User',
                'email'         => 'admin@example.com',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'is_active'     => true,
                'department_id' => $itDept?->id,
                'position'      => 'Admin',
                'spatie_role'   => 'admin',
            ],
            // ─── Requester ──────────────────────────────────────────────
            [
                'name'          => 'John Doe',
                'email'         => 'john@example.com',
                'password'      => Hash::make('password'),
                'role'          => 'requester',
                'is_active'     => true,
                'department_id' => $itDept?->id,
                'position'      => 'Supervisor',
                'spatie_role'   => 'requester',
            ],
            // ─── Section Head (Approver L1) ─────────────────────────────
            [
                'name'          => 'Budi Santoso',
                'email'         => 'section.head@example.com',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $itDept?->id,
                'position'      => 'Section Head',
                'spatie_role'   => 'section_head',
            ],
            // ─── Division Head (Approver L2) ────────────────────────────
            [
                'name'          => 'Siti Rahayu',
                'email'         => 'division.head@example.com',
                'password'      => Hash::make('password'),
                'role'          => 'division_head',
                'is_active'     => true,
                'department_id' => $opsDept?->id,
                'position'      => 'Manager',
                'spatie_role'   => 'division_head',
            ],
            // ─── Finance Admin (Approver L3) ────────────────────────────
            [
                'name'          => 'Manager Finance',
                'email'         => 'manager@example.com',
                'password'      => Hash::make('password'),
                'role'          => 'finance_admin',
                'is_active'     => true,
                'department_id' => $finDept?->id,
                'position'      => 'Finance Manager',
                'spatie_role'   => 'finance_admin',
            ],
            // ─── Treasurer (Approver L4 / Final) ────────────────────────
            [
                'name'          => 'Ahmad Treasurer',
                'email'         => 'treasurer@example.com',
                'password'      => Hash::make('password'),
                'role'          => 'treasurer',
                'is_active'     => true,
                'department_id' => $finDept?->id,
                'position'      => 'Treasurer',
                'spatie_role'   => 'treasurer',
            ],
            // ─── Procurement Staff (Admin / PIC) ────────────────────────
            [
                'name'          => 'Procurement Staff',
                'email'         => 'procurement@example.com',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'is_active'     => true,
                'department_id' => $finDept?->id,
                'position'      => 'Admin',
                'spatie_role'   => 'admin',
            ],
        ];

        foreach ($users as $userData) {
            // Pisahkan spatie_role dari data user
            $spatieRole = $userData['spatie_role'];
            unset($userData['spatie_role']);

            // Buat atau temukan user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign Spatie role (untuk filament-shield)
            // syncRoles agar tidak duplikat jika dijalankan ulang
            $user->syncRoles([$spatieRole]);
        }

        // Update department section head
        if ($itDept) {
            $itDept->update([
                'section_head_id' => User::where('email', 'section.head@example.com')->value('id'),
            ]);
        }

        $this->command->info('✅ Users berhasil dibuat & Spatie roles di-assign.');
    }
}
