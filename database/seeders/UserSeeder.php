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

        $superAdminRole = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'admin')
            ->first();

        if ($superAdminRole && $email !== '' && $password !== '') {
            $admin = User::query()->updateOrCreate(['email' => $email], [
                'name' => trim((string) env('ADMIN_NAME', 'Quản trị DNT Bắc Ninh')),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            // Tài khoản quản trị cấu hình trong env luôn có quyền super admin.
            $admin->assignRole($superAdminRole);
        }

        if ($superAdminRole && app()->environment('local')) {
            $demoAdmin = User::query()->updateOrCreate([
                'email' => (string) env('DNT_DEMO_ADMIN_EMAIL', 'admin@dnt-seed.example'),
            ], [
                'name' => 'Quản trị demo DNT Bắc Ninh',
                'password' => Hash::make((string) env('DNT_DEMO_ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            $demoAdmin->assignRole($superAdminRole);
        }
    }
}
