<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Buat dan simpan role
        $roles = ['Admin', 'Headbar', 'Headkitchen', 'Bar', 'Kitchen'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Satu akun staff realistis per role, password sama untuk kemudahan demo/testing.
        $staff = [
            ['name' => 'Super Admin', 'email' => 'superadmin@gmail.com', 'role' => 'Admin'],
            ['name' => 'Dimas Pratama', 'email' => 'headbar@merra.test', 'role' => 'Headbar'],
            ['name' => 'Rani Kusuma', 'email' => 'headkitchen@merra.test', 'role' => 'Headkitchen'],
            ['name' => 'Bagas Saputra', 'email' => 'bar@merra.test', 'role' => 'Bar'],
            ['name' => 'Siti Aminah', 'email' => 'kitchen@merra.test', 'role' => 'Kitchen'],
        ];

        foreach ($staff as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('Admin123')]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
