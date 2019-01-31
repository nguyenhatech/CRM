<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeTableCgroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cgroups', function (Blueprint $table) {
            $table->tinyInteger('type')->after('name')->default(0)->comment('Kiểu nhóm: ví dụ: 0 thông thường, 1 nhóm chương trình giới thiệu bạn bè');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cgroups', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
