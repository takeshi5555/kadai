<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');        // ID 
            $table->unsignedBigInteger('matching_id'); // マッチングID (FK) 
            $table->unsignedBigInteger('reviewer_id'); // 送信ユーザーID (FK) 
            $table->unsignedBigInteger('reviewee_id'); // 受信ユーザーID (FK) 
            $table->tinyInteger('rating')->comment('1~5の整数'); // 評価 (1~5)
            $table->text('comment')->nullable(); // コメント 
            $table->timestamps();               // created_at, updated_at

            $table->foreign('matching_id')->references('id')->on('matchings')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade'); // usersテーブルへのFK
            $table->foreign('reviewee_id')->references('id')->on('users')->onDelete('cascade'); // usersテーブルへのFK
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
