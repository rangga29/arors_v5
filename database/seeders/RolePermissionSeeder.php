<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // reset cached roles and permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'view logs']);

        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        Permission::create(['name' => 'view clinics']);
        Permission::create(['name' => 'create clinics']);
        Permission::create(['name' => 'edit clinics']);
        Permission::create(['name' => 'delete clinics']);

        Permission::create(['name' => 'view schedule dates']);
        Permission::create(['name' => 'create schedule dates']);
        Permission::create(['name' => 'edit schedule dates']);

        Permission::create(['name' => 'view schedules']);
        Permission::create(['name' => 'download schedules']);
        Permission::create(['name' => 'update schedules']);
        Permission::create(['name' => 'delete schedules']);

        Permission::create(['name' => 'view appointments']);
        Permission::create(['name' => 'update appointments']);

        Permission::create(['name' => 'view schedules history']);

        //create roles and assign existing permissions
        $sisfoRole = Role::create(['name' => 'sisfo']);
        $sisfoRole->givePermissionTo('view logs');

        $sisfoRole->givePermissionTo('view users');
        $sisfoRole->givePermissionTo('create users');
        $sisfoRole->givePermissionTo('edit users');
        $sisfoRole->givePermissionTo('delete users');

        $sisfoRole->givePermissionTo('view clinics');
        $sisfoRole->givePermissionTo('create clinics');
        $sisfoRole->givePermissionTo('edit clinics');
        $sisfoRole->givePermissionTo('delete clinics');

        $sisfoRole->givePermissionTo('view schedule dates');
        $sisfoRole->givePermissionTo('create schedule dates');
        $sisfoRole->givePermissionTo('edit schedule dates');

        $sisfoRole->givePermissionTo('view schedules');
        $sisfoRole->givePermissionTo('download schedules');
        $sisfoRole->givePermissionTo('update schedules');
        $sisfoRole->givePermissionTo('delete schedules');

        $sisfoRole->givePermissionTo('view appointments');
        $sisfoRole->givePermissionTo('update appointments');

        $sisfoRole->givePermissionTo('view schedules history');


        $rmRole = Role::create(['name' => 'rm']);

        $rmRole->givePermissionTo('view clinics');
        $rmRole->givePermissionTo('create clinics');
        $rmRole->givePermissionTo('edit clinics');
        $rmRole->givePermissionTo('delete clinics');

        $rmRole->givePermissionTo('view schedule dates');
        $rmRole->givePermissionTo('create schedule dates');
        $rmRole->givePermissionTo('edit schedule dates');

        $rmRole->givePermissionTo('view schedules');
        $rmRole->givePermissionTo('download schedules');
        $rmRole->givePermissionTo('update schedules');
        $rmRole->givePermissionTo('delete schedules');

        $rmRole->givePermissionTo('view appointments');
        $rmRole->givePermissionTo('update appointments');

        $rmRole->givePermissionTo('view schedules history');


        $csRole = Role::create(['name' => 'cs']);
        $csRole->givePermissionTo('view clinics');

        $csRole->givePermissionTo('view schedule dates');

        $csRole->givePermissionTo('view schedules');

        $csRole->givePermissionTo('view appointments');

        $csRole->givePermissionTo('view schedules history');


        // gets all permissions via Gate::before rule
        $adminRole = Role::create(['name' => 'administrator']);

        // create user
        $adminUser = User::create([
            'name' => 'Administrator',
            'username' => 'administrator',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $adminUser->assignRole($adminRole);

        $sisfoUser = User::create([
            'name' => 'Sisfo',
            'username' => 'sisfo',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $sisfoUser->assignRole($sisfoRole);

        $rmUser = User::create([
            'name' => 'Rekam Medis',
            'username' => 'rekam_medis',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $rmUser->assignRole($rmRole);

        $csUser = User::create([
            'name' => 'Customer Service',
            'username' => 'customer_service',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $csUser->assignRole($csRole);

        $farmasiUser = User::create([
            'name' => 'Farmasi',
            'username' => 'farmasi',
            'password' => '$2y$12$vCQ3znlunuEkaljyLxar9O5zbE/GviZ/NPGU.uc9NFNHu9EHR7mdq',
            'remember_token' => Str::random(10),
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $farmasiUser->assignRole($csRole);

        $rm1User = User::create([
            'name' => 'YOHANES SANTO SUHIANTO',
            'username' => '198213026',
            'password' => '$2y$12$IPuuip7Uvyyu./BVHfiPGOoh6AgS7phi/4Q2zxgpI5SJ/utuMFqVG',
            'remember_token' => Str::random(10),
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $rm1User->assignRole($rmRole);

        $rm2User = User::create([
            'name' => 'FRANSISCA WAHYU ANDANTI, A.MD.RMIKs',
            'username' => '199113140',
            'password' => '$2y$12$e7tKjGQbJ8AX2nScry0.2OJssYrcLXPtdWfOZjf80rURPV04Oz3HC',
            'remember_token' => Str::random(10),
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $rm2User->assignRole($rmRole);

    }
}
