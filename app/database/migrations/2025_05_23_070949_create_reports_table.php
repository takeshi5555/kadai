<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id');             // ID
            $table->unsignedBigInteger('reporting_user_id'); // 通報ユーザーID (FK) 
            $table->unsignedBigInteger('reported_user_id');  // 被通報ユーザーID (FK) 
            $table->unsignedInteger('reason_id');                 // 理由 
            $table->tinyInteger('status')->default(0)->comment('0:unprocessed, 1:processed'); // ステータス
            $table->timestamps();                    // created_at, updated_at

            $table->foreign('reporting_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reported_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reason_id')->references('id')->on('report_reasons')->onDelete('restrict'); // 理由が削除されたら通報も削除されないようにrestrict
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
