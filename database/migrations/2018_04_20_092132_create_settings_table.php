<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->text('special_day')->nullable(); // Ngày đặc biệt
            $table->tinyInteger('disable_promotion_special_day')->default(1); // Ko cho KM ngày đặc biệt
            $table->tinyInteger('disable_sms_special_day')->default(1); // Ko cho gửi tin nhắn sms KM ngày đặc biệt
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
        Schema::dropIfExists('settings');
    }
}
