<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_friends', function (Blueprint $table) {
            $table->increments('id');
            $table->char('phone_owner', 12)->comment('Số điện thoại người giới thiệu.');
            $table->char('phone_friend', 12)->unique()->comment('Số điện thoại bạn bè được giới thiệu.');
            $table->boolean('is_customer')->default(false)->comment('Bạn bè đã là khách hàng của hệ thống');
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
        Schema::dropIfExists('invite_friends');
    }
}
