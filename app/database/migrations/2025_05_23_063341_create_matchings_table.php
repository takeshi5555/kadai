<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matchings', function (Blueprint $table) {
            $table->bigIncrements('id');           // ID
            $table->unsignedBigInteger('offering_skill_id'); // 提供スキルID 
            $table->unsignedBigInteger('receiving_skill_id'); // 受講スキルID
            $table->tinyInteger('status')->default(0)->comment('0:pending, 1:confirmed, 2:completed, 3:canceled'); // ステータス
            $table->dateTime('scheduled_at')->nullable()->comment('Google Calendar API連携用'); // 予約日時 
            $table->timestamps();           

            $table->foreign('offering_skill_id')->references('id')->on('skills')->onDelete('cascade');
            $table->foreign('receiving_skill_id')->references('id')->on('skills')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matchings');
    }
}
