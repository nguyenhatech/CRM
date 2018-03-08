<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name'); // tên tỉnh thành phố
            $table->string('short_name')->nullable(); // tên viết tắt tỉnh tp
            $table->string('code')->nullable(); // mã bưu chính tỉnh tp
            $table->tinyInteger('priority')->default(0); // mã bưu chính tỉnh tp
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
        Schema::dropIfExists('cities');
    }
}
