<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Skill;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        Log::info('ReportController@store method started.');

        try { // ★ここからtryブロックを開始
            $request->validate([
                'reportable_type' => 'required|string',
                'reportable_id' => 'required|integer',
                'reason_id' => 'required|exists:report_reasons,id', // 大まかな理由
                'sub_reason_id' => 'nullable|exists:report_reasons,id', // 詳細な理由 (nullable)
                'comment' => 'nullable|string|max:1000',
                'reported_user_id' => 'nullable|exists:users,id',
            ]);
            Log::info('ReportController@store validation passed.');

            // 通報対象のモデルクラスが存在するか確認
            $modelClass = $request->input('reportable_type');
            if (!class_exists($modelClass)) {
                Log::warning("Invalid reportable_type: {$modelClass}");
                return back()->with('error', '無効な通報対象です。');
            }

            // 通報対象の存在を確認
            $reportable = $modelClass::find($request->input('reportable_id'));
            if (!$reportable) {
                Log::warning("Reportable not found: type={$modelClass}, id={$request->input('reportable_id')}");
                return back()->with('error', '通報対象が見つかりません。');
            }

            // 自分が自分を通報するのを防ぐ (通報対象がUserモデルの場合などを考慮)
            if ($request->input('reported_user_id') && Auth::id() == $request->input('reported_user_id')) {
                Log::warning("Attempt to report self by user_id: ".Auth::id());
                return back()->with('error', '自分自身を通報することはできません。');
            }
            // コンテンツ所有者が自身を通報するのを防ぐ例 (必要であれば)
            if (($reportable instanceof Skill && $reportable->user_id == Auth::id()) ||
                ($reportable instanceof Message && $reportable->sender_id == Auth::id())) {
                Log::warning("Attempt to report own content by user_id: ".Auth::id()." type:".$modelClass);
                return back()->with('error', '自身のコンテンツを通報することはできません。');
            }

            Report::create([
                'reporting_user_id' => Auth::id(),
                'reported_user_id' => $request->input('reported_user_id'),
                'reportable_type' => $modelClass,
                'reportable_id' => $request->input('reportable_id'),
                'reason_id' => $request->input('reason_id'),
                'sub_reason_id' => $request->input('sub_reason_id'),
                'comment' => $request->input('comment'),
                'status' => 'unprocessed',
            ]);
            Log::info('Report created successfully.'); // ★このログが出るか、出ないか？

            return back()->with('success', '通報が送信されました。ご協力ありがとうございます。');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // バリデーションエラーはここでキャッチ
            Log::error('Validation Error during report store: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) { // ★ここが重要！その他の例外をキャッチ
            // DBエラー、未定義の変数アクセスなど、あらゆるPHP/Laravelのエラーをここで捕らえる
            Log::error('Error in ReportController@store: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return back()->with('error', '通報処理中にエラーが発生しました。');
        }
    }
}