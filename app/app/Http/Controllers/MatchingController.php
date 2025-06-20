<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\Matching;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class MatchingController extends Controller
{
    public function apply($targetSkillId)
    {
        // 相手のスキル情報を取得
        $targetSkill = Skill::with('user')->findOrFail($targetSkillId);

        // ログイン中のユーザーのスキルを取得
        $mySkills = Auth::user()->skills;

        // --- 相手のスキル提供者（ユーザー）の情報 ---
        $targetUser = $targetSkill->user;

        // 提供者の全マッチング件数 (提供・受領の両方)
        $targetUserTotalOfferedMatchingsCount = $targetUser->offeredMatchings()->whereIn('status', [1, 2])->count();
        $targetUserTotalReceivedMatchingsCount = $targetUser->receivedMatchings()->whereIn('status', [1, 2])->count();
        $targetUserTotalMatchingCount = $targetUserTotalOfferedMatchingsCount + $targetUserTotalReceivedMatchingsCount;

        // 提供者の全レビューの評価平均 (reviewee_id がこのユーザーであるレビューの平均)
        $targetUserAverageRating = $targetUser->reviewsReceived()->avg('rating');

        // 提供者に対する最新レビュー (reviewee_id がこのユーザーであるレビューの最新3件)
        $targetUserLatestReviews = $targetUser->reviewsReceived()->latest()->limit(3)->get();

        return view('matching.matching_apply', [
            'targetSkill' => $targetSkill,
            'mySkills' => $mySkills,
            'targetUserTotalMatchingCount' => $targetUserTotalMatchingCount,
            'targetUserAverageRating' => $targetUserAverageRating,
            'targetUserLatestReviews' => $targetUserLatestReviews,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'offering_skill_id' => 'required|exists:skills,id',
            'receiving_skill_id' => 'required|exists:skills,id',
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i'],
        ]);

        $offering = Skill::find($request->offering_skill_id);
        $receiving = Skill::find($request->receiving_skill_id);

        // セッションに一時保存
        Session::put('matching_data', [
            'offering_skill_id' => $offering->id,
            'receiving_skill_id' => $receiving->id,
            'scheduled_at' => $request->scheduled_at,
        ]);

        return view('matching.matching_apply_conf', [
            'offering' => $offering,
            'receiving' => $receiving,
            'scheduledAt' => $request->scheduled_at,
            'offeringId' => $request->offering_skill_id,
            'receivingId' => $request->receiving_skill_id,
        ]);
    }

    public function store(Request $request)
    {
        $data = Session::get('matching_data');

        if (!$data || !isset($data['offering_skill_id']) || !isset($data['receiving_skill_id']) || !isset($data['scheduled_at'])) {
            return redirect('/skill/search')->with('error', '不正なリクエストです。');
        }

        // DB保存
        Matching::create([
            'offering_skill_id' => $data['offering_skill_id'],
            'receiving_skill_id' => $data['receiving_skill_id'],
            'status' => 0, // 0:保留中
            'scheduled_at' => $data['scheduled_at'],
        ]);

        Session::forget('matching_data');

        return redirect('/matching/history')->with('message', 'マッチング申請を送信しました。');
    }

    public function approve($id)
    {
        $matching = Matching::findOrFail($id);

        // 承認は、マッチング申請を「受けた側」（＝あなたに申請されたマッチングで、あなたが提供するスキルを持つユーザー）が行う
        // receivingSkill の user_id が、現在ログインしているユーザーの ID と一致するかチェック
        if ($matching->receivingSkill->user_id !== auth()->id()) {
            abort(403, 'このマッチングを承認する権限がありません。');
        }

        $matching->status = 1; // 1:承認済み
        $matching->save();

        return redirect('/matching/history')->with('message', 'マッチングを承認しました。');
    }

    public function reject($id)
    {
        $matching = Matching::findOrFail($id);

        // 拒否も、マッチング申請を「受けた側」（＝あなたに申請されたマッチングで、あなたが提供するスキルを持つユーザー）が行う
        // receivingSkill の user_id が、現在ログインしているユーザーの ID と一致するかチェック
        if ($matching->receivingSkill->user_id !== auth()->id()) {
            abort(403, 'このマッチングを拒否する権限がありません。');
        }

        $matching->status = 4; // 4:拒否
        $matching->save();

        // 拒否後にマイページに戻る場合は、ここを '/mypage' に変更
        return redirect('/matching/history')->with('message', 'マッチングを拒否しました。');
    }

 

    public function history()
    {
        $userId = Auth::id();

        // 自分が申請したマッチング：自分が提供するスキル（offeringSkill）を元に申請したもの
        $applied = Matching::whereHas('offeringSkill', function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        })
                        // ステータスが 3 (キャンセル) および 4 (拒否) でない条件を追加
                        ->whereNotIn('status', [3, 4]) // ★ ここを修正 ★
                        ->with([
                            'offeringSkill',
                            'receivingSkill',
                            'myReview',      // 自分が書いたレビュー
                            'partnerReview', // 相手が書いたレビュー
                            'offeringSkill.user', // offeringSkillの所有者（＝申請者）
                            'receivingSkill.user' // receivingSkillの所有者（＝受領者）
                        ])
                        ->orderBy('created_at', 'desc')
                        ->get();

        // あなたに申請されたマッチング：相手が提供するスキル（receivingSkill）に対して申請されたもの
        $received = Matching::whereHas('receivingSkill', function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        })
                        // ステータスが 3 (キャンセル) および 4 (拒否) でない条件を追加
                        ->whereNotIn('status', [3, 4]) // ★ ここを修正 ★
                        ->with([
                            'offeringSkill',
                            'receivingSkill',
                            'myReview',      // 自分が書いたレビュー
                            'partnerReview', // 相手が書いたレビュー
                            'offeringSkill.user',
                            'receivingSkill.user'
                        ])
                        ->orderBy('created_at', 'desc')
                        ->get();

        $reviewedIds = []; // この変数の用途によってはロジックの追加が必要です

        return view('matching.matching_history', [
            'applied' => $applied,
            'received' => $received,
            'reviewedIds' => $reviewedIds,
        ]);
    }


    public function cancel($id)
    {
        $matching = Matching::findOrFail($id);

        // キャンセルは、マッチングを「申請した側」（＝offeringSkillのユーザー）が行う
        if ($matching->offeringSkill->user_id !== auth()->id()) {
            abort(403, 'このマッチング申請を取り消す権限がありません。');
        }

        $matching->status = 3; // 3:キャンセル
        $matching->save();

        return redirect('/mypage')->with('message', 'マッチング申請を取り消しました。');
    }

    public function complete($id)
    {
        $matching = Matching::findOrFail($id);
        $userId = auth()->id();

        // 完了は、申請者（offeringSkillのユーザー）または受領者（receivingSkillのユーザー）のどちらでも行える
        if (
            $matching->offeringSkill->user_id !== $userId &&
            $matching->receivingSkill->user_id !== $userId
        ) {
            abort(403, 'このマッチングを完了する権限がありません。');
        }

        // 承認済みのみ完了可能
        if ($matching->status !== 1) {
            return redirect('/matching/history')->with('error', '完了できるのは承認済みのマッチングのみです。');
        }

        $matching->status = 2; // 2:完了
        $matching->save();

        return redirect('/matching/history')->with('message', 'マッチングを完了しました。レビューを投稿できます。');
    }
}