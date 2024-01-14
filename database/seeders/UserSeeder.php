<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create permission
        $user_list = Permission::create([ "name"=> "user_list"]);
        $user_view = Permission::create([ "name"=> "user_view"]);
        $user_create = Permission::create([ "name"=> "user_create"]);
        $user_update = Permission::create([ "name"=> "user_update"]);
        $user_delete = Permission::create([ "name"=> "user_delete"]);

        // create Role for admin
        $admin_role = Role::create([ "name"=> "admin"]);

        // give all persion to admin
        $admin_role->givePermissionTo([
            $user_list,
            $user_view,
            $user_create,
            $user_update,
            $user_delete,
        ]);

        // create Admin as user
        $admin = User::create([
            "username"=> "Admin",
            "phone_number"=> "087909661",
            "email"=> "admin@gmail.com",
            "password"=> bcrypt("123456"),
        ]);

        // Assign all roles to admin
        $admin->assignRole($admin_role);
        $admin->givePermissionTo([
            $user_list,
            $user_view,
            $user_create,
            $user_update,
            $user_delete,
        ]);

        // create Role for normal user
        $user_role = Role::create([ "name"=> "counter"]);

        // give all persion to normal user
        $user_role->givePermissionTo([
            $user_list,
            $user_view,
            $user_update,
        ]);

        // create Admin as normal user
        $user = User::create([
            "username"=> "user",
            "phone_number"=> "85593268990",
            "email"=> "user@gmail.com",
            "password"=> bcrypt("123456"),
        ]);

        // Assign all roles to normal user
        $user->assignRole($user_role);
        $user->givePermissionTo([
            $user_list,
            $user_view,
            $user_create,
            $user_update,
        ]);
    }
}
