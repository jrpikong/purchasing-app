<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------------
        // 1. Buat semua permissions secara langsung (tanpa artisan)
        // -------------------------------------------------------
        $this->createPermissions();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------------
        // 2. Buat Spatie roles & assign permissions
        // -------------------------------------------------------
        $this->createRoles();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createPermissions(): void
    {
        // Standard CRUD actions untuk tiap resource
        $resourceActions = [
            'ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny',
            'Restore', 'RestoreAny', 'ForceDelete', 'ForceDeleteAny',
            'Replicate', 'Reorder',
        ];

        // Resource models (sesuai Policy & naming Shield)
        $resources = ['PurchaseRequest', 'User', 'Department', 'Vendor'];

        foreach ($resources as $resource) {
            foreach ($resourceActions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$action}:{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Role management (filament-shield bawaan)
        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $action) {
            Permission::firstOrCreate([
                'name'       => "{$action}:Role",
                'guard_name' => 'web',
            ]);
        }

        // Page permissions (format Shield: page_{ClassName})
        $pages = ['Dashboard', 'UserGuide'];
        foreach ($pages as $page) {
            Permission::firstOrCreate([
                'name'       => "page_{$page}",
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('   → ' . Permission::where('guard_name', 'web')->count() . ' permissions siap.');
    }

    private function createRoles(): void
    {
        $all = Permission::where('guard_name', 'web')->pluck('name');

        // ── super_admin: semua permissions ────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($all);

        // ── admin: semua permissions ───────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($all);

        // ── requester: buat & lihat PR miliknya ───────────────────
        $requester = Role::firstOrCreate(['name' => 'requester', 'guard_name' => 'web']);
        $requester->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereIn('name', [
                    'ViewAny:PurchaseRequest',
                    'View:PurchaseRequest',
                    'Create:PurchaseRequest',
                    'Update:PurchaseRequest',
                    'page_Dashboard',
                ])
                ->pluck('name')
        );

        // ── approver roles: lihat & proses PR ─────────────────────
        $approverPerms = Permission::where('guard_name', 'web')
            ->whereIn('name', [
                'ViewAny:PurchaseRequest',
                'View:PurchaseRequest',
                'Update:PurchaseRequest',
                'page_Dashboard',
            ])
            ->pluck('name');

        foreach (['section_head', 'division_head', 'finance_admin', 'treasurer', 'approver'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($approverPerms);
        }

        // ── panel_user: akses dasar (tanpa permissions khusus) ────
        Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);

        $this->command->info('   → ' . Role::where('guard_name', 'web')->count() . ' roles siap.');
    }
}
