<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogleTokensToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_access_token', 1000)->nullable(); // 長いトークンを保存するため
            $table->string('google_refresh_token', 255)->nullable();
            $table->bigInteger('google_expires_in')->nullable(); // Unixタイムスタンプで有効期限を保存
            $table->string('google_scope', 255)->nullable();
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                'google_access_token',
                'google_refresh_token',
                'google_expires_in',
                'google_scope',
            ]);
            //
        });
    }
}
