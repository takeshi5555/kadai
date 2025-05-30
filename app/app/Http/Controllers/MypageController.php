<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skill;
use App\Models\Matching;
use App\Models\Message;
use App\Models\User;

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ユーザーが登録したスキル
        $skills = $user->skills;

        // ユーザーが関わる全てのマッチング履歴を取得
        // ユーザーが提供するスキルが関わるマッチング
        $offeredSkillIds = $user->skills->pluck('id')->toArray();
        $offeredMatchings = collect(); // 空のコレクションで初期化

        if (!empty($offeredSkillIds)) {
            $offeredMatchings = Matching::whereIn('offering_skill_id', $offeredSkillIds)
                                        ->with([
                                            'offeringSkill',
                                            'receivingSkill',
                                            'offerUser',
                                            'requestUser',
                                            'myReview',
                                            'reviewFromPartner'
                                        ])
                                        ->get();
        }


        // ユーザーがリクエストするスキルが関わるマッチング
        // ここでは、ユーザーが持つスキルの中から、リクエスト側として関わるスキルを探す
        // 実際には、ユーザーがリクエストした（相手の）スキルに対するマッチングを取得する必要があるため、
        // より複雑なクエリが必要になる場合があります。
        // 例えば、ユーザーがリクエストしたスキルIDのリストを取得し、それを使ってマッチングを検索する、など。
        // 現在のモデルでは、`requestUser`リレーションが定義されているため、そちらを利用して取得します。
        $requestedMatchings = Matching::whereHas('requestUser', function ($query) use ($user) {
                                            $query->where('users.id', $user->id);
                                        })
                                    ->with([
                                        'offeringSkill',
                                        'receivingSkill',
                                        'offerUser',
                                        'requestUser',
                                        'myReview',
                                        'reviewFromPartner'
                                    ])
                                    ->get();

                                    
        // 両方のコレクションを結合し、重複を排除して最新のものからソート
         $offeredMatchings->each(function ($matching) {
            $matching->statusText = $this->getMatchingStatusText($matching->status);
        });
        $requestedMatchings->each(function ($matching) {
            $matching->statusText = $this->getMatchingStatusText($matching->status);
        });

        // 未読メッセージのカウント
        $unreadMessagesCount = $user->receivedMessages()->whereNull('read_at')->count();

        return view('mypage.index', compact('user', 'skills', 'offeredMatchings', // 自分が提供したスキル関連のマッチング
            'requestedMatchings', 'unreadMessagesCount'));
    }

     private function getMatchingStatusText($status)
    {
        switch ($status) {
            case 0: return '保留中';
            case 1: return '承認済み';
            case 2: return '完了';
            case 3: return 'キャンセル';
            default: return '不明';
        }
    }
}