<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id')->unsigned()->index();
            $table->string('uuid', 50)->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('fax')->nullable();
            $table->tinyInteger('sex')->default(-1);
            $table->string('facebook_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('website')->nullable();
            $table->date('dob')->nullable();
            $table->string('job')->nullable();
            $table->string('address')->nullable();
            $table->string('company_address')->nullable();
            $table->tinyInteger('level')->default(0);
            $table->tinyInteger('source')->default(0);
            $table->string('avatar')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('customers');
    }
}
