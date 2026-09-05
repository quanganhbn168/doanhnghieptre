<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    private const ADMIN_GUARD = 'admin';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(AssociationRoleSeeder::class);
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Guard web dành cho frontend; role của Filament phải thuộc guard admin.
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => self::ADMIN_GUARD,
        ]);

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => self::ADMIN_GUARD,
        ]);

        $permissions = Permission::query()
            ->where('guard_name', self::ADMIN_GUARD)
            ->pluck('name');

        $adminRole->syncPermissions($permissions);
        $superAdminRole->syncPermissions($permissions);
    }
}
