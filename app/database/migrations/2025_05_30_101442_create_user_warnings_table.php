<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // 警告を受けたユーザー
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // 警告を発行した管理者（nullable: 管理者アカウント削除時も警告履歴を残すため）
            $table->foreignId('report_id')->nullable()->constrained('reports')->onDelete('set null'); // 関連する通報ID（nullable: 通報を経由しない警告もありうるため）
            $table->text('message'); // 警告メッセージ本文
            $table->string('type')->default('warning'); // 警告の種類（例: 'warning', 'suspension_notice' など）
            $table->timestamp('warned_at')->useCurrent(); // 警告発行日時
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_warnings');
    }
}