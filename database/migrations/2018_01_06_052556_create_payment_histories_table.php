<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->increments('id')->unsigned()->index();
            $table->integer('client_id')->unsigned()->index()->default(0);
            $table->integer('customer_id')->unsigned()->index();
            $table->string('uuid', 50)->nullable();
            $table->text('description');
            $table->double('total_amount')->default(0); // Số tiền giao dịch
            $table->double('total_point')->default(0); // Số điểm thưởng giao dịch
            $table->date('payment_at')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('type')->default(0);
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
        Schema::dropIfExists('payment_histories');
    }
}
