<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SeetingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('settings')->insert([
            [
                'special_day' => '',
                'disable_promotion_special_day' => 1,
                'disable_sms_special_day' => 1,
                'disable_sms_special_day' => 1,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now()
            ]
        ]);
    }
}
