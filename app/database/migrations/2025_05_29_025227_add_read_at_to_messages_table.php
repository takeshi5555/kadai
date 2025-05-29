<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReadAtToMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // read_at カラムを timestamp 型で追加し、nullを許容します。
            // after('content') は content カラムの後に配置する指示ですが、必須ではありません。
            $table->timestamp('read_at')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            // ロールバック時に read_at カラムを削除します
            $table->dropColumn('read_at');
        });
    }
}