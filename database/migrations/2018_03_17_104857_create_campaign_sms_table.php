<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_sms', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('campaign_id');
            $table->string('sms_id');
            $table->integer('total')->default(0);
            $table->integer('success')->default(0);
            $table->integer('fail')->default(0);
            $table->tinyInteger('done')->default(0);
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
        Schema::dropIfExists('campaign_sms');
    }
}
