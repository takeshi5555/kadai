<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 既存の 'messages' テーブルに変更を加えます
        Schema::table('messages', function (Blueprint $table) {
            // Laravelの標準的なタイムスタンプカラムである created_at と updated_at を追加します。
            // これらはLaravelによって自動的に管理されます。
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
        // down メソッドでは、up メソッドで行った変更を元に戻します。
        Schema::table('messages', function (Blueprint $table) {
            // 追加したタイムスタンプカラムを削除します。
            $table->dropTimestamps();
        });
    }
}