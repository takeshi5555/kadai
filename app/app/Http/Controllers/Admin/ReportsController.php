<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\UserWarning; // ★追加★
use App\Models\Skill; // 関連モデル（必要に応じて）
use App\Models\Message; // 関連モデル（必要に応じて）
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // ★追加★
use Illuminate\Support\Facades\DB; // ★追加★
use Illuminate\Support\Facades\Log; // ★追加★
use Illuminate\Notifications\Notifiable;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['reportingUser', 'reportedUser', 'reason', 'subReason'])
                         ->orderBy('created_at', 'desc');

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        } else {
            // デフォルトで「未処理」の通報のみ表示する場合
            $query->where('status', 'unprocessed');
            // または、全ての通報を表示したい場合は下記のように変更
            // $query->whereIn('status', ['unprocessed', 'processed', 'ignored']);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('reportingUser', function($q_user) use ($search) {
                            $q_user->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('reportedUser', function($q_user) use ($search) {
                            $q_user->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('reason', function($q_reason) use ($search) {
                            $q_reason->where('reason_text', 'like', "%{$search}%");
                        })
                        ->orWhereHas('subReason', function($q_sub_reason) use ($search) {
                            $q_sub_reason->where('reason_text', 'like', "%{$search}%");
                        })
                        ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $reports = $query->paginate(10);
        return view('admin.reports.index', compact('reports'));
    }

    public function show(Report $report)
    {
        // 通報対象のモデルを動的にロード (もし `reportedUser` 以外に通報対象がある場合)
        // 現在のControllerとBladeの構造だと、reportedUser が主なので、この部分は必須ではないかもしれません
        $reportable = null;
        if ($report->reportable_type && $report->reportable_id) {
            if (class_exists($report->reportable_type)) {
                $reportable = $report->reportable_type::find($report->reportable_id);
            }
        }
        return view('admin.reports.show', compact('report', 'reportable'));
    }

    /**
     * 通報ステータスを更新する（AJAXリクエスト用）
     * このメソッドはPUT/PATCHリクエストを受け、JSONレスポンスを返します。
     * admin/reports/{report} にPUTリクエストとしてルーティングされます。
     */
    public function update(Request $request, Report $report)
    {
        $request->validate([
            'status' => ['required', Rule::in(['unprocessed', 'processed', 'ignored'])],
        ]);

        try {
            $report->status = $request->input('status');
            $report->save();

            // 成功時はJSONレスポンスを返す
            return response()->json(['success' => true, 'message' => '通報ステータスが更新されました。']);
        } catch (\Exception $e) {
            Log::error("Failed to update report status: " . $e->getMessage(), ['report_id' => $report->id, 'status' => $request->input('status')]);
            // エラー時もJSONレスポンスを返す
            return response()->json(['success' => false, 'message' => 'ステータス更新に失敗しました。'], 500);
        }
    }

    /**
     * ユーザーに警告メッセージを送信し、その履歴を保存する
     * このメソッドはPOSTリクエストを受け、JSONレスポンスを返します。
     * admin/reports/{report}/warn にPOSTリクエストとしてルーティングされます。
     */
    public function warnUser(Request $request, Report $report)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message_content' => 'required|string|max:1000', // このフィールド名がフォームのinput nameと一致しているか確認
        ]);

        $user = User::find($request->user_id);
        $adminId = Auth::id(); // 現在ログインしている管理者のIDを取得

        if (!$user) {
            return redirect()->back()->with('error', '警告対象ユーザーが見つかりません。');
        }

        DB::beginTransaction();
        try {
            // ★UserWarning モデルを使ってデータを保存★
            UserWarning::create([
                'user_id' => $user->id,
                'admin_id' => $adminId, // 警告を発行した管理者のID
                'report_id' => $report->id, // 関連する通報ID
                'message' => $request->message_content, // 警告メッセージ
                'type' => 'general_warning', // 警告のタイプ (例: general_warning, content_violation など)
                'warned_at' => now(), // 現在時刻
            ]);

            // オプション: 通報ステータスを「処理済み」に更新
            $report->status = 'processed';
            $report->save();

            DB::commit();

            return redirect()->route('admin.reports.index')->with('success', 'ユーザーに警告が送信され、通報ステータスが更新されました。');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('警告送信エラー (UserWarning): ' . $e->getMessage()); // ログメッセージも修正
            return redirect()->back()->with('error', '警告の送信中にエラーが発生しました。');
        }
    }


}