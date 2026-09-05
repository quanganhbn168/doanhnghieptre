<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssociationRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'association_manager' => ['association.review', 'association.manage', 'content.moderate'],
            'chapter_manager' => ['chapters.receive'],
            'membership_head' => ['memberships.ratify'],
            'communications_manager' => ['content.moderate'],
        ] as $name => $permissions) {
            $role = Role::findOrCreate($name, 'admin');
            foreach ($permissions as $permission) {
                $role->givePermissionTo(Permission::findOrCreate($permission, 'admin'));
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
