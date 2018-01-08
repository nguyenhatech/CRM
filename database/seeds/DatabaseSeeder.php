<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Nh\User::class, 1)->create([
            'name'     => 'Administrator',
            'email'    => 'admin@admin.com',
            'phone'    => '123456789',
            'password' => bcrypt('123456'),
            'status'   => 1
        ]);
        // $this->call(UsersTableSeeder::class);
    }
}
