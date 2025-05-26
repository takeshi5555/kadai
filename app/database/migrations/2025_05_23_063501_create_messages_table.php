<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');        // ID 
            $table->unsignedBigInteger('matching_id'); // マッチングID (FK)
            $table->unsignedBigInteger('sender_id');   // 送信ユーザーID (FK)
            $table->unsignedBigInteger('receiver_id'); // 受信ユーザーID (FK) 
            $table->text('content');            // 内容
            $table->dateTime('sent_at');        // 送信日時 

            $table->foreign('matching_id')->references('id')->on('matchings')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade'); // usersテーブルへのFK
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade'); // usersテーブルへのFK
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
