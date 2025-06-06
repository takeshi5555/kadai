<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReadAtToUserWarningsTable extends Migration
{
    public function up()
    {
        Schema::table('user_warnings', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('type'); // または適切な位置
        });
    }

    public function down()
    {
        Schema::table('user_warnings', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
}