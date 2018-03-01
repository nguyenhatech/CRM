<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Nh\Repositories\Roles\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // factory(Nh\User::class, 1)->create([
        //     'name'     => 'Administrator',
        //     'email'    => 'admin@admin.com',
        //     'phone'    => '123456789',
        //     'password' => bcrypt('123456'),
        //     'status'   => 1
        // ]);

        \DB::table('roles')->insert([
            [
                'name'         => 'superadmin',
                'display_name' => 'Super Admin',
                'description'  => 'Quyền tối thượng',
                'type'         => Role::TYPE_SYSTEM,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now()
            ],
            [
                'name'         => 'system.admin',
                'display_name' => 'Admin hệ thống',
                'description'  => 'Quyền truy cập admin hệ thống',
                'type'         => Role::TYPE_SYSTEM,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now()
            ]
        ]);

        \DB::table('role_user')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
            ]
        ]);
        // $this->call(UsersTableSeeder::class);
    }
}
