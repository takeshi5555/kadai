<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportReason;

class InitialReportReasonsSeeder extends Seeder
{
    public function run()
    {
        // 第一階層の理由をまず追加
        $reason1 = ReportReason::firstOrCreate(['reason_text' => 'ヘイト']);
        $reason2 = ReportReason::firstOrCreate(['reason_text' => '攻撃的な行為']);
        $reason3 = ReportReason::firstOrCreate(['reason_text' => '自殺や自傷行為']);
        $reason4 = ReportReason::firstOrCreate(['reason_text' => 'センシティブな内容']);

        // 第二階層の理由を追加（parent_id を設定）
        ReportReason::firstOrCreate(['reason_text' => '中傷や差別的比喩', 'parent_id' => $reason1->id]);
        ReportReason::firstOrCreate(['reason_text' => 'ヘイト行為への言及', 'parent_id' => $reason1->id]);
        ReportReason::firstOrCreate(['reason_text' => '非人道的な扱い', 'parent_id' => $reason1->id]);

        ReportReason::firstOrCreate(['reason_text' => '望ましくない閲覧注意コンテンツ', 'parent_id' => $reason2->id]);
        ReportReason::firstOrCreate(['reason_text' => '特定のユーザーへの嫌がらせ', 'parent_id' => $reason2->id]);
        ReportReason::firstOrCreate(['reason_text' => '侮辱的発言', 'parent_id' => $reason2->id]);
        ReportReason::firstOrCreate(['reason_text' => '暴力的出来事の否定', 'parent_id' => $reason2->id]);

        ReportReason::firstOrCreate(['reason_text' => '自傷行為または自殺を働きかけるよう他人に求めている', 'parent_id' => $reason3->id]);
        ReportReason::firstOrCreate(['reason_text' => '自傷行為を助長する情報を共有している', 'parent_id' => $reason3->id]);
        ReportReason::firstOrCreate(['reason_text' => '自傷行為または自殺の意向をほのめかしている', 'parent_id' => $reason3->id]);

        ReportReason::firstOrCreate(['reason_text' => '刺激の強いコンテンツ', 'parent_id' => $reason4->id]);
        ReportReason::firstOrCreate(['reason_text' => '暴力的な性行為', 'parent_id' => $reason4->id]);
        ReportReason::firstOrCreate(['reason_text' => '性的行動', 'parent_id' => $reason4->id]);
    }
}
