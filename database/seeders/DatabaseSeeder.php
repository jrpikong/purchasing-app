<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            VendorSeeder::class,
            UserSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📋 Default Users:');
        $this->command->info('   Admin: admin@example.com / password');
        $this->command->info('   User: john@example.com / password');
        $this->command->info('   Manager: manager@example.com / password');
        $this->command->info('   PIC: procurement@example.com / password');
        $this->command->info('');
        $this->command->info('🚀 Access admin panel: http://localhost:8000/admin');
    }
}
