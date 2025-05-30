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
            $table->id();
            $table->foreignId('reporting_user_id')->constrained('users')->onDelete('cascade'); // 通報したユーザー

            // 通報対象の特定
            $table->string('reportable_type'); // 通報対象のモデル名 (e.g., 'App\Models\Skill', 'App\Models\Message')
            $table->unsignedBigInteger('reportable_id'); // 通報対象のID (e.g., skill_id, message_id)
            $table->index(['reportable_type', 'reportable_id']); // ポリモーフィックリレーションのインデックス

            // 通報されたユーザー (ユーザープロフィール通報時など)
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->onDelete('cascade');

            $table->foreignId('reason_id')->constrained('report_reasons'); // 大まかな通報理由
            $table->foreignId('sub_reason_id')->nullable()->constrained('report_reasons'); // より具体的な通報理由

            $table->text('comment')->nullable(); // 自由記述のコメント

            $table->string('status')->default('unprocessed'); // 0: unprocessed, 1: processed を文字列に変更

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
        Schema::dropIfExists('reports');
    }
}
