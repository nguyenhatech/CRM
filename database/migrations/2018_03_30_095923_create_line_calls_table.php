<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLineCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 50)->nullable();
            $table->string('vendor', 50)->nullable()->default('123cs');
            $table->integer('user_id')->nullable()->default(0);
            $table->integer('line');
            $table->string('phone_account')->nullable();
            $table->string('email_account')->nullable();
            $table->string('password')->nullable();
            $table->string('profile_id')->nullable();
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
        Schema::dropIfExists('line_calls');
    }
}
