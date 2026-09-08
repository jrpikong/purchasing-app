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

        /*
        |--------------------------------------------------------------------------
        | Departments
        |--------------------------------------------------------------------------
        */

        $bomDept  = Department::where('code', 'BOM')->first();
        $commDept = Department::where('code', 'COMM')->first();
        $musDept  = Department::where('code', 'MUS')->first();
        $opsDept  = Department::where('code', 'OPS')->first();
        $comDept  = Department::where('code', 'COM')->first();

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        |
        | User diambil berdasarkan struktur organisasi pada organizational chart.
        |
        | Vacant / Person 1 / Person 2 tidak dibuat sebagai user.
        |
        */

        $users = [

            // =================================================================
            // BOARD OF MANAGEMENT
            // =================================================================

            [
                'name'          => 'Christine Kristiati',
                'email'         => 'chair@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'super_admin',
                'is_active'     => true,
                'department_id' => $bomDept?->id,
                'position'      => 'President',
                'spatie_role'   => 'super_admin',
            ],

            [
                'name'          => 'Claudia Prawirakoesoemah',
                'email'         => 'secretary@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'is_active'     => true,
                'department_id' => $bomDept?->id,
                'position'      => 'Secretary',
                'spatie_role'   => 'admin',
            ],

            [
                'name'          => 'Syandra Kwan',
                'email'         => 'treasurer@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'treasurer',
                'is_active'     => true,
                'department_id' => $bomDept?->id,
                'position'      => 'Treasurer',
                'spatie_role'   => 'treasurer',
            ],

            // =================================================================
            // COMMUNICATION DIVISION
            // =================================================================

            [
                'name'          => 'Luthfia Nurhikmah',
                'email'         => 'publicrelations@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $commDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Mutia Anggita Putri',
                'email'         => 'events@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $commDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Ellen Viola Sugiharto',
                'email'         => 'publishing@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $commDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            // =================================================================
            // MUSEUM DIVISION
            // =================================================================

            [
                'name'          => 'Rima Sjoekri',
                'email'         => 'vcmuseums@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'division_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Head of Museum Division',
                'spatie_role'   => 'division_head',
            ],

            [
                'name'          => 'Borbála Csete',
                'email'         => 'projects@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Nader Sobhan',
                'email'         => 'museumtours@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Anne-Sophie Wislocki',
                'email'         => 'museumtours2@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Stephanie Tantri',
                'email'         => 'museumtraining@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Yuliani Tedja',
                'email'         => 'museumtraining2@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Wine Amanda',
                'email'         => 'languages@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Carmel Valencia Indrawan',
                'email'         => 'languages2@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Mutiara Patmosantjojo',
                'email'         => 'schools@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $musDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            // =================================================================
            // OPERATIONS DIVISION
            // =================================================================

            [
                'name'          => 'Ardiansyah Nugraha',
                'email'         => 'it@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'is_active'     => true,
                'department_id' => $opsDept?->id,
                'position'      => 'IT Support',
                'spatie_role'   => 'admin',
            ],

            [
                'name'          => 'Nida',
                'email'         => 'library@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'is_active'     => true,
                'department_id' => $opsDept?->id,
                'position'      => 'Library Admin',
                'spatie_role'   => 'admin',
            ],

            [
                'name'          => 'Yesnita',
                'email'         => 'library2@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'is_active'     => true,
                'department_id' => $opsDept?->id,
                'position'      => 'Finance Admin',
                'spatie_role'   => 'admin',
            ],

            // =================================================================
            // COMMUNITY DIVISION
            // =================================================================

            [
                'name'          => 'Jasmine Hafiza',
                'email'         => 'explorers@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Nick Hughes',
                'email'         => 'heritagetours@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Lily J. van Bunnik',
                'email'         => 'studygroups@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'See Mun Leong-Suparno',
                'email'         => 'rumahku@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Clare Jeremy Roberts',
                'email'         => 'rumahku2@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            // =================================================================
            // LANGUAGE SPEAKING SECTIONS
            // =================================================================

            [
                'name'          => 'Machiko Shimizu',
                'email'         => 'japanesesection@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Mayumi Kudo',
                'email'         => 'japanesesection2@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Hee Eun (Elyse) Kim',
                'email'         => 'koreansection@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],

            [
                'name'          => 'Hyun Suk Park',
                'email'         => 'koreansection2@heritagejkt.org',
                'password'      => Hash::make('password'),
                'role'          => 'section_head',
                'is_active'     => true,
                'department_id' => $comDept?->id,
                'position'      => 'Co-Chair',
                'spatie_role'   => 'section_head',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create / Update Users
        |--------------------------------------------------------------------------
        */

        foreach ($users as $userData) {
            $spatieRole = $userData['spatie_role'];

            unset($userData['spatie_role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->syncRoles([$spatieRole]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Division Heads
        |--------------------------------------------------------------------------
        */

        if ($commDept) {
            $commDept->update([
                'section_head_id' => null,
            ]);
        }

        if ($musDept) {
            $musDept->update([
                'section_head_id' => User::where(
                    'email',
                    'vcmuseums@heritagejkt.org'
                )->value('id'),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        $this->command->info(
            '✅ Client users berhasil dibuat & Spatie roles berhasil di-assign.'
        );
    }
}
