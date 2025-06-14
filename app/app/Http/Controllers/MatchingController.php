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
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use App\Http\Controllers\GoogleCalendarController;
use Google\Service\Calendar;

class MatchingController extends Controller
{
    public function apply($targetSkillId)
    {
        // 相手のスキル情報を取得
        $targetSkill = Skill::with('user')->findOrFail($targetSkillId);

        // ログイン中のユーザーのスキルを取得
        $mySkills = Auth::user()->skills; // UserモデルにhasMany('Skill')リレーションが必要

        // --- 相手のスキル提供者（ユーザー）の情報 ---
        $targetUser = $targetSkill->user; // 相手のスキル提供者のユーザー情報

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
    $scheduledAt = $request->input('scheduled_at');

    if (!$data || !$scheduledAt) {
        dd('セッションがありません');
        return redirect('/skill/search')->with('error', '不正なリクエストです。');
    }
    /*
    // Googleカレンダーへ登録
    try {
        $googleCalendarController = new GoogleCalendarController();
        $client = $googleCalendarController->getGoogleClientForUser(); // ここで認証済みクライアントを取得

        if (!$client) {
            // クライアントがnullの場合（例: トークンがない、リフレッシュ失敗、ユーザー未ログイン）
            // getGoogleClientForUser() 内でリダイレクトされるべきですが、念のため
            return redirect()->route('google.auth')->with('warning', 'Googleカレンダーとの連携が必要です。');
        }

        // clientは既にアクセストークンが設定され、必要ならリフレッシュされている状態
        $service = new Calendar($client); // Google\Service\Calendar を使用

        $event = new Calendar\Event([ // Google\Service\Calendar\Event を使用
            'summary' => 'SkillSwap マッチング予定',
            'description' => 'SkillSwapでのスキル交換予定',
            'start' => [
                'dateTime' => date(DATE_RFC3339, strtotime($request->scheduled_at)),
                'timeZone' => 'Asia/Tokyo',
            ],
            'end' => [
                'dateTime' => date(DATE_RFC3339, strtotime($request->scheduled_at) + 3600), // 1時間後のイベント
                'timeZone' => 'Asia/Tokyo',
            ],
            'attendees' => [
                ['email' => Auth::user()->email], // 申請者のメールアドレス
                // 相手のスキル所有者のメールアドレスも追加する場合は以下のように
                // ['email' => $offering->user->email], // $offeringは事前に取得しておく
            ],
            'reminders' => [
                'useDefault' => FALSE,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 30],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
            // 'conferenceData' => [ // ビデオ会議リンクを自動生成したい場合
            //     'createRequest' => [
            //         'requestId' => uniqid(),
            //         'conferenceSolutionKey' => [
            //             'type' => 'hangoutsMeet',
            //         ],
            //     ],
            // ],
        ]);

        $calendarId = 'primary'; // ユーザーのメインカレンダー
        // $event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]); // conferenceDataを使う場合
        $event = $service->events->insert($calendarId, $event); // conferenceDataを使わない場合

        Log::info('Google Calendar event created: ' . $event->htmlLink);
        // マッチングモデルにカレンダーイベントIDを保存する（後で更新・削除に使う）
        // $matching->google_calendar_event_id = $event->id;
        // $matching->save();

        return redirect()->route('dashboard')->with('success', 'マッチング申請が完了し、Googleカレンダーにイベントが追加されました！');

    } catch (\Exception $e) {
        Log::error('Google Calendar 登録失敗: ' . $e->getMessage()); // Log::warning ではなく Log::error が適切
        return redirect()->back()->with('error', 'カレンダーイベントの作成に失敗しました。詳細: ' . $e->getMessage());
    }
//gooogle
*/

    // DB保存
    Matching::create([
        'offering_skill_id' => $data['offering_skill_id'],
        'receiving_skill_id' => $data['receiving_skill_id'],
        'status' => 0,
        'scheduled_at' => $scheduledAt,
    ]);

    Session::forget('matching_data');
    return redirect('/matching/history')->with('message', 'Googleカレンダーにも登録されました。');


    $request->validate([
        'offering_skill_id' => 'required|exists:skills,id',
        'receiving_skill_id' => 'required|exists:skills,id',
        'scheduled_at' => 'required|date',
    ]);

    Matching::create([
        'offering_skill_id' => $request->offering_skill_id,
        'receiving_skill_id' => $request->receiving_skill_id,
        'status' => 0,
        'scheduled_at' => $request->scheduled_at,
    ]);

    return redirect('/matching/history')->with('message', 'マッチング申請を送信しました。');

    }

public function approve($id)
    {
        $matching = Matching::findOrFail($id);

        // 承認は、マッチング申請を「受けた側」（＝あなたに申請されたマッチングで、あなたが提供するスキルを持つユーザー）が行う
        // receivingSkill の user_id が、現在ログインしているユーザーの ID と一致するかチェック
        if ($matching->receivingSkill->user_id !== auth()->id()) {
            // 権限がない場合は403エラー
            abort(403, 'このマッチングを承認する権限がありません。');
        }

        $matching->status = 1; // 承認
        $matching->save();

        return redirect('/matching/history')->with('message', 'マッチングを承認しました。');
    }

    public function reject($id)
    {
        $matching = Matching::findOrFail($id);

        // 拒否も、マッチング申請を「受けた側」（＝あなたに申請されたマッチングで、あなたが提供するスキルを持つユーザー）が行う
        // receivingSkill の user_id が、現在ログインしているユーザーの ID と一致するかチェック
        if ($matching->receivingSkill->user_id !== auth()->id()) {
            // 権限がない場合は403エラー
            abort(403, 'このマッチングを拒否する権限がありません。');
        }

        $matching->status = 3; // 拒否
        $matching->save();

        return redirect('/matching/history')->with('message', 'マッチングを拒否しました。');
    }

    public function history()
    {
        $userId = Auth::id();
        // 自分が申請したマッチング：自分が提供するスキル（offeringSkill）を元に申請したもの
        $applied = Matching::whereHas('offeringSkill', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->with([
                    'offeringSkill',
                    'receivingSkill',
                    'myReview',      // 自分が書いたレビュー
                    'partnerReview', // 相手が書いたレビュー
                    // applicantUser, recipientUser は Matchingモデルにリレーションがない場合はエラーになります。
                    // もしこれらのリレーションがUserモデルへの直接的なリレーションであれば問題ありませんが、
                    // offeringSkill.user, receivingSkill.user を使うのが一般的です。
                    'offeringSkill.user', // offeringSkillの所有者（＝申請者）
                    'receivingSkill.user' // receivingSkillの所有者（＝受領者）
                ])
                ->orderBy('created_at', 'desc')
                ->get();

        // あなたに申請されたマッチング：相手が提供するスキル（receivingSkill）に対して申請されたもの
        $received = Matching::whereHas('receivingSkill', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->with([
                    'offeringSkill',
                    'receivingSkill',
                    'myReview',      // 自分が書いたレビュー
                    'partnerReview', // 相手が書いたレビュー
                    // こちらも同様に offeringSkill.user, receivingSkill.user を推奨
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
            // 権限がない場合は403エラー
            abort(403, 'このマッチング申請を取り消す権限がありません。');
        }

        $matching->status = 4; // status 4 を「キャンセル」と定義している場合
        $matching->save();

        // もし物理的にレコードを削除したい場合は以下のコメントを外す（上の一行は削除）
        // $matching->delete();

        return redirect('/matching/history')->with('message', 'マッチング申請を取り消しました。');
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

        $matching->status = 2; // 完了
        $matching->save();

        return redirect('/matching/history')->with('message', 'マッチングを完了しました。レビューを投稿できます。');
    }
}

//完了」ボタン → status = 2 に更新（レビュー入力解放）

//レビューフォームの表示条件追加

//承認されたマッチングだけを Googleカレンダーに登録