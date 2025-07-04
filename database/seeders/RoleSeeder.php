<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Worker', 'Team Lead', 'Department Head', 'Finance', 'Procurement', 'Warehouse'];

        foreach ($roles as $role) {
           // Role::firstOrCreate(['name' => $role]);

         $email = Str::slug($role, '_') . '@example.com'; // Məs: team_lead@example.com

            // İstifadəçi yoxdursa yarad
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $role, 'password' => bcrypt('password')]
            );

            $user->assignRole($role);
        }

    }
}