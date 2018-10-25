<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLimitTimeTypeColumnToPromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotions', function($table) {
            $table->tinyInteger('limit_time_type')->default(1)->after('date_end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('limit_time_type');
        });
    }
}
