<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->nullable();
            $table->string('name');
            $table->date('dob')->nullable();
            $table->tinyInteger('gender')->default(0);
            $table->integer('customer_id')->default(0);
            $table->integer('owner_id')->default(0);
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->integer('city_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('facebook')->nullable();
            $table->tinyInteger('quality')->default(0);
            $table->tinyInteger('source')->default(0);
            $table->string('utm_source')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('leads');
    }
}
