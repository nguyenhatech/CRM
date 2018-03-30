<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneCallHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_call_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0);
            $table->string('agent_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('from', 15);
            $table->string('to', 15);
            $table->string('hotline', 15)->nullable();
            $table->tinyInteger('type')->default(0); // mobile|voice_device
            $table->tinyInteger('call_type')->default(1); // call_in|call_out
            $table->tinyInteger('status')->default(0);
            $table->string('start_time', 15)->nullable()->default(0);
            $table->string('end_time', 15)->nullable()->default(0);
            $table->tinyInteger('stop_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phone_call_histories');
    }
}
