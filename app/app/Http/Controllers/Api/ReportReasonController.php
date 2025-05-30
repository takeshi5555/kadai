<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportReason;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReportReasonController extends Controller
{
    public function getChildren($id)
    {
        $startTime = microtime(true); // パフォーマンス計測開始

        // 直接、parent_id が $id のレコードをクエリする
        // 必要なカラムだけを選択し、インデックスが利用されるようにする
        $children = ReportReason::where('parent_id', $id)
                                ->select('id', 'reason_text') // 最適化: 必要なカラムのみ
                                ->get();

        // 親理由が存在しない場合（$idに該当する親理由がない場合）は子理由も存在しない
        // ReportReason::find($id) のチェックはここでは不要になるが、
        // 厳密性を求めるなら含めても良い (その場合、2クエリになる)
        // 例: $parentReason = ReportReason::find($id); if (!$parentReason) { return response()->json([], 404); }

        $endTime = microtime(true); // パフォーマンス計測終了
        $executionTime = ($endTime - $startTime);
        Log::info("API/ReportReasonController@getChildren execution time for parent_id {$id}: {$executionTime} seconds");

        return response()->json($children);
    }
}