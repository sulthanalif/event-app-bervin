<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roleSuperAdmin = Role::create(['name' => 'super-admin']);
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleBse = Role::create(['name' => 'bse']);


        $permissions = [
            'roulette',
            'dashboard',
            'manage-users',
            'settings',
            'manage-permissions',
            'manage-roles',
            'manage-logs',

            'manage-dealers',
            'manage-products',
            'manage-budget-period',
            'manage-budget-period-create',
            'manage-special-voucher',
            'manage-voucher',
            'manage-special-voucher-form',
            'manage-sellout',
            'manage-sales',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $roleSuperAdmin->givePermissionTo($permissions);
        $roleAdmin->givePermissionTo([
            'roulette',
            'dashboard',

            'manage-dealers',
            'manage-products',
            'manage-budget-period',
            'manage-budget-period-create',
            'manage-special-voucher',
            'manage-voucher',
            'manage-special-voucher-form',
            'manage-sellout',
            'manage-sales',
        ]);
        $roleBse->givePermissionTo([
            'roulette',
        ]);

        $superAdmin = User::factory()->create([
            'name' => 'IT BERVIN',
            'email' => 'it@bervin.co.id',
            'password' => 'An9gr3k!!',
        ]);

        $superAdmin->assignRole($roleSuperAdmin);

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@bervin.co.id',
            'password' => 'Bervin123',
        ]);

        $admin->assignRole($roleAdmin);

        $bse = User::factory()->create([
            'name' => 'bse',
            'email' => 'bse@bervin.co.id',
            'password' => 'Bervin123',
        ]);

        $bse->assignRole($roleBse);


        $this->call([
            ImportSeeder::class,
        ]);

    }
}
