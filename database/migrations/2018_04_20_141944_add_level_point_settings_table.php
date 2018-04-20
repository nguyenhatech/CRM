<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLevelPointSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('level_normal')->after('disable_sms_special_day')->default(0);
            $table->integer('level_sliver')->after('level_normal')->default(10000);
            $table->integer('level_gold')->after('level_sliver')->default(25000);
            $table->integer('level_diamond')->after('level_gold')->default(50000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('level_normal');
            $table->dropColumn('level_sliver');
            $table->dropColumn('level_gold');
            $table->dropColumn('level_diamond');
        });
    }
}
