<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id')->unsigned();
            $table->string('code');
            $table->tinyInteger('type')->unsigned();
            $table->double('amount')->unsigned();
            $table->double('amount_max')->unsigned()->default(0);
            $table->integer('quantity')->unsigned()->default(0);
            $table->integer('quantity_per_user')->unsigned()->default(0);
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->tinyInteger('status')->unsigned()->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
