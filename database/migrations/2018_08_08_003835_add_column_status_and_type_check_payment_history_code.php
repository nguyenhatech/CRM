<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStatusAndTypeCheckPaymentHistoryCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_history_codes', function (Blueprint $table) {
            $table->smallInteger('type_check')->default(0);
            $table->smallInteger('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_history_codes', function (Blueprint $table) {
            //
        });
    }
}
