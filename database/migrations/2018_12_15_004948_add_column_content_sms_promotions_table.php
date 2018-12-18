<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnContentSmsPromotionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('sms_template', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('sms_template');
        });
    }
}
