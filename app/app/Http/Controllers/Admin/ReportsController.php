<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User; // 関連モデル
use App\Models\Skill; // 関連モデル
use App\Models\Message; // 関連モデル
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['reportingUser', 'reportedUser', 'reason', 'subReason'])
                       ->orderBy('created_at', 'desc');

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        } else {
            // デフォルトでは未処理のみ表示する、または全て表示する
            $query->where('status', 'unprocessed'); // または $query->whereIn('status', ['unprocessed', 'pending']);
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
        // 通報対象のモデルを動的にロード
        $reportable = null;
        if ($report->reportable_type && $report->reportable_id) {
            if (class_exists($report->reportable_type)) {
                $reportable = $report->reportable_type::find($report->reportable_id);
            }
        }
        return view('admin.reports.show', compact('report', 'reportable'));
    }

    public function update(Request $request, Report $report)
    {
        $request->validate([
            'status' => ['required', Rule::in(['unprocessed', 'processed', 'ignored'])], // 適切なステータスを定義
        ]);

        $report->status = $request->input('status');
        $report->save();

        // 必要に応じて、通報対象のユーザーやコンテンツへの自動的な措置をここに記述
        // 例: 通報が処理済みになり、ユーザーに問題がある場合、ユーザーを停止する
        // if ($request->input('status') === 'processed' && $report->reportedUser) {
        //     // $report->reportedUser->is_active = false;
        //     // $report->reportedUser->save();
        // }

        return redirect()->route('admin.reports.index')->with('success', '通報ステータスが更新されました。');
    }

    // destroy メソッドは、通報記録自体を削除する機能ですが、通常は残しておくと良いでしょう。
    // public function destroy(Report $report)
    // {
    //     $report->delete();
    //     return redirect()->route('admin.reports.index')->with('success', '通報記録が削除されました。');
    // }
}