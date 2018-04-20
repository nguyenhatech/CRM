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
                'special_day'                   => '30-04, 01-05, 02-09',
                'disable_promotion_special_day' => 1,
                'disable_sms_special_day'       => 1,
                'level_normal'                  => 0,
                'level_sliver'                  => 10000,
                'level_gold'                    => 25000,
                'level_diamond'                 => 50000,
                'created_at'                    => Carbon::now(),
                'updated_at'                    => Carbon::now()
            ]
        ]);
    }
}
