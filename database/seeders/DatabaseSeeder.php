<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Urutan seeder PENTING:
     *  1. Department  — dibutuhkan oleh User
     *  2. Vendor      — master data
     *  3. Shield      — buat Spatie permissions & roles (harus sebelum User)
     *  4. User        — buat users & assign Spatie roles
     *  5. ApprovalFlow  — butuh departments
     *  6. ApprovalLevel — butuh approval flows
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            VendorSeeder::class,
            ShieldSeeder::class,
            UserSeeder::class,
            ApprovalFlowSeeder::class,
            ApprovalLevelSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║         DATABASE SEEDED SUCCESSFULLY ✅              ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  Users (password: password)                          ║');
        $this->command->info('║  ┌─ Super Admin  : jr.pikong@gmail.com               ║');
        $this->command->info('║  ├─ Admin        : admin@example.com                 ║');
        $this->command->info('║  ├─ Requester    : john@example.com                  ║');
        $this->command->info('║  ├─ Section Head : section.head@example.com          ║');
        $this->command->info('║  ├─ Div Head     : division.head@example.com         ║');
        $this->command->info('║  ├─ Finance      : manager@example.com               ║');
        $this->command->info('║  ├─ Treasurer    : treasurer@example.com             ║');
        $this->command->info('║  └─ Procurement  : procurement@example.com           ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  Approval Flows                                      ║');
        $this->command->info('║  ├─ Standard   (s/d 10 jt)  : Section Head           ║');
        $this->command->info('║  ├─ Management (10–50 jt)   : +Division Head          ║');
        $this->command->info('║  └─ Executive  (>50 jt)     : +Finance+Treasurer      ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  Panel: http://localhost:8000/admin                  ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
    }
}
