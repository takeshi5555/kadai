<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Matching;
use App\Models\Review;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * エクスポートフォーム（モーダル）を表示するためのメソッド。
     * 実際には、モーダルはBladeに直接記述されるため、このメソッドは単純にビューを返すか、
     * マイページ表示時に必要なデータを渡す役割になります。
     * 今回はルーティングに登録するのみで、特別なビューは返しません。
     *
     * @return \Illuminate\View\View
     */
    public function showExportForm()
    {
       
        return redirect()->route('profile.edit');
    }


    /**
     * マッチング履歴をCSVでエクスポートする。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportMatchingHistory(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status_filter' => 'nullable|array', // 複数選択可能
            'status_filter.*' => 'in:0,1,2,3', // 0:申請中, 1:承認済み, 2:完了, 3:拒否
        ]);

        $user = Auth::user();
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $status_filters = $request->input('status_filter', []); // フィルターがなければ空配列

        // 自分が提供者または受領者であるマッチングを取得
        $matchings = Matching::where(function ($query) use ($user) {
                $query->whereHas('offeringSkill.user', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->orWhereHas('receivingSkill.user', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            // 必要なリレーションをEager Loading
            ->with([
                'offeringSkill.user',
                'receivingSkill.user',
                'reviews' => function ($query) use ($user) {
                    $query->where('reviewer_id', '!=', $user->id); // 相手からのレビューのみ取得
                }
            ])
            ->when($start_date, function ($query, $date) {
                $query->where('scheduled_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($end_date, function ($query, $date) {
                $query->where('scheduled_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->when(!empty($status_filters), function ($query) use ($status_filters) {
                $query->whereIn('status', $status_filters);
            })
            ->orderBy('scheduled_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="matching_history_' . Carbon::now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($matchings, $user) {
            $file = fopen('php://output', 'w');

            // BOM (Byte Order Mark) を追加してExcelでの文字化けを防ぐ
            fputs($file, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($file, [
                'マッチングID',
                'マッチング日時',
                'ステータス',
                '交換相手ユーザー名',
                '交換相手の評価平均', 
                '自分が提供したスキル',
                '相手が提供したスキル',
                '相手からのレビュー内容',
                '相手からの評価スコア',
            ]);

            foreach ($matchings as $match) {
                // 交換相手の特定
                $partner = null;
                if ($match->offeringSkill->user->id === $user->id) {
                    // 自分が提供者
                    $partner = $match->receivingSkill->user;
                } else {
                    // 自分が受領者
                    $partner = $match->offeringSkill->user;
                }

                $partnerUserName = $partner->name ?? '不明';
                // 相手からのレビュー
                $partnerReview = $match->reviews->first(function ($review) use ($partner) {
                    return $review->reviewer_id === $partner->id;
                });

                $reviewComment = $partnerReview ? $partnerReview->comment : 'なし';
                $reviewRating = $partnerReview ? $partnerReview->rating : 'なし';
                $reviewCreatedAt = $partnerReview ? Carbon::parse($partnerReview->created_at)->format('Y年m月d日 H:i') : 'なし';

                // ステータス表示
                $statusText = '';
                switch ($match->status) {
                    case 0: $statusText = '申請中'; break;
                    case 1: $statusText = '承認済み'; break;
                    case 2: $statusText = '完了'; break;
                    case 3: $statusText = '拒否'; break;
                    default: $statusText = '不明'; break;
                }

                // データの書き込み
                fputcsv($file, [
                    $match->id,
                    $match->scheduled_at ? Carbon::parse($match->scheduled_at)->format('Y年m月d日 H:i') : '未定',
                    $statusText,
                    $partnerUserName,
                    $match->offeringSkill->title ?? '不明',
                    $match->receivingSkill->title ?? '不明',
                    $reviewComment,
                    $reviewRating,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}