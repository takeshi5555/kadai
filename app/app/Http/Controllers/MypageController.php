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
use Illuminate\Validation\Rule;

class MypageController extends Controller
{


    public function updateUserInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // 自身のメールアドレスは除外
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('mypage.index')->with('success', 'ユーザー情報を更新しました。');
    }


    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $skills = $user->skills;

        // --- あなたが申し込んでいるマッチング ---
        // offeringSkill が自分のスキルに紐づき、かつステータスが保留中(0)または承認済み(1)のもの
        $appliedMatchings = Matching::whereHas('offeringSkill', function ($query) use ($userId) {
                                $query->where('user_id', $userId);
                            })
                            // 相手のスキルも明確にするため receivingSkill が相手のスキルであることを追加
                            ->whereHas('receivingSkill', function ($query) use ($userId) {
                                $query->where('user_id', '!=', $userId);
                            })
                            ->whereIn('status', [0, 1]) // ★ ここを追加: ステータスが保留中(0)または承認済み(1) ★
                            ->with([
                                'offeringSkill.user',
                                'receivingSkill.user',
                                'myReview',
                                'partnerReview'
                            ])
                            ->orderByDesc('created_at')
                            ->get();


        // --- 相手から申し込まれているマッチング ---
        // receivingSkill が自分のスキルに紐づき、かつステータスが保留中(0)または承認済み(1)のもの
        $receivedMatchings = Matching::whereHas('receivingSkill', function ($query) use ($userId) {
                                $query->where('user_id', $userId);
                            })
                            // 相手のスキルも明確にするため offeringSkill が相手のスキルであることを追加
                            ->whereHas('offeringSkill', function ($query) use ($userId) {
                                $query->where('user_id', '!=', $userId);
                            })
                            ->whereIn('status', [0, 1]) // ★ ここを追加: ステータスが保留中(0)または承認済み(1) ★
                            ->with([
                                'offeringSkill.user',
                                'receivingSkill.user',
                                'myReview',
                                'partnerReview'
                            ])
                            ->orderByDesc('created_at')
                            ->get();

        // statusText の設定
        $receivedMatchings->each(function ($matching) {
            $matching->statusText = $this->getMatchingStatusText($matching->status);
        });

        $appliedMatchings->each(function ($matching) {
            $matching->statusText = $this->getMatchingStatusText($matching->status);
        });

        $unreadMessagesCount = $user->receivedMessages()->whereNull('read_at')->count();

        $unreadWarnings = $user->warnings()
                               ->whereNull('read_at')
                               ->with(['report.reason', 'report.subReason'])
                               ->orderBy('created_at', 'desc')
                               ->get();

        $readWarnings = $user->warnings()
                             ->whereNotNull('read_at')
                             ->with(['report.reason', 'report.subReason'])
                             ->orderBy('read_at', 'desc')
                             ->get();

        return view('mypage.index', compact(
            'user',
            'skills',
            'appliedMatchings',
            'receivedMatchings',
            'unreadMessagesCount',
            'unreadWarnings',
            'readWarnings'
        ));
    }

    public function markWarningAsRead(UserWarning $warning)
    {
        if (Auth::id() !== $warning->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $warning->read_at = Carbon::now();
        $warning->save();

        return redirect()->back()->with('success', '警告を確認済みにしました。');
    }

    // MatchingControllerのステータス定義に合わせるため、拒否(4)も追加
    private function getMatchingStatusText($status)
    {
        switch ($status) {
            case 0: return '保留中';
            case 1: return '承認済み';
            case 2: return '完了';
            case 3: return 'キャンセル';
            case 4: return '拒否';
            default: return '不明';
        }
    }
}