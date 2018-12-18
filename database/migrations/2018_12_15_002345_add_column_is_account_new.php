<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsAccountNew extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->boolean('is_account_new')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('is_account_new');
        });
    }
}
