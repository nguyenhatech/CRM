<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('customer_id')->unsigned(); // Mã khách hàng
            $table->integer('survey_id')->unsigned(); // Mã cuộc khảo sát
            $table->integer('type')->unsigned()->default(1); // Loại: do gửi mail hay CSKH gọi điện ...
            $table->string('title')->nullable(); // Tiêu đề : ví dụ: khách hàng phản hồi về chuyến đi mã abc
            $table->string('note')->nullable(); // Ghi chú thêm của khách
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
        Schema::dropIfExists('feedbacks');
    }
}
