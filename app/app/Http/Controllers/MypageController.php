<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Skill;
use App\Models\Matching;
use App\Models\Message;
use App\Models\User;
use App\Models\UserWarning;
use App\Models\Report;
use App\Models\ReportReason;

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ユーザーが登録したスキル
        $skills = $user->skills;

        // ... マッチング履歴と未読メッセージの取得ロジックはそのまま ...
        $offeredSkillIds = $user->skills->pluck('id')->toArray();
        $offeredMatchings = collect();

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

        $offeredMatchings->each(function ($matching) {
            $matching->statusText = $this->getMatchingStatusText($matching->status);
        });
        $requestedMatchings->each(function ($matching) {
            $matching->statusText = $this->getMatchingStatusText($matching->status);
        });

        $unreadMessagesCount = $user->receivedMessages()->whereNull('read_at')->count();


        // ★★★ ここを修正/追加：未確認の警告と確認済みの警告を分けて取得 ★★★
        $unreadWarnings = $user->warnings()
                               ->whereNull('read_at') // read_at が NULL のもの（未確認）
                               ->with(['report.reason', 'report.subReason'])
                               ->orderBy('created_at', 'desc')
                               ->get();

        $readWarnings = $user->warnings()
                             ->whereNotNull('read_at') // read_at が NULL ではないもの（確認済み）
                             ->with(['report.reason', 'report.subReason'])
                             ->orderBy('read_at', 'desc') // 確認された日時でソート
                             ->get();



        return view('mypage.index', compact(
            'user',
            'skills',
            'offeredMatchings',
            'requestedMatchings',
            'unreadMessagesCount',
            'unreadWarnings', // ★追加：未確認の警告
            'readWarnings'    // ★追加：確認済みの警告

        ));
    }

    // ★★★ ここから追加：警告を「確認済み」にするメソッド ★★★
    public function markWarningAsRead(UserWarning $warning)
    {
        // ログイン中のユーザーが、この警告の対象ユーザーであることを確認
        if (Auth::id() !== $warning->user_id) {
            abort(403, 'Unauthorized action.'); // 許可されていない操作
        }

        // read_at を現在時刻に設定して保存
        $warning->read_at = Carbon::now();
        $warning->save();

        return redirect()->back()->with('success', '警告を確認済みにしました。');
    }
    // ★★★ ここまで追加 ★★★

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