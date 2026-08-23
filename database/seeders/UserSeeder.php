<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = trim((string) env('ADMIN_EMAIL'));
        $password = (string) env('ADMIN_PASSWORD');

        if ($email === '' || $password === '') {
            return;
        }

        $superAdminRole = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'admin')
            ->first();

        if ($superAdminRole) {
            $admin = User::query()->updateOrCreate(['email' => $email], [
                'name' => trim((string) env('ADMIN_NAME', 'Quản trị DNT Bắc Ninh')),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            // Tài khoản quản trị cấu hình trong env luôn có quyền super admin.
            $admin->assignRole($superAdminRole);
        }
    }
}
