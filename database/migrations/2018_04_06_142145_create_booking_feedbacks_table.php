<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('booking_id');
            $table->integer('customer_id');
            $table->double('score', 2, 2);
            $table->dateTime('confirmed_at');
            $table->tinyInteger('confirmed_by');
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
        Schema::dropIfExists('booking_feedbacks');
    }
}
