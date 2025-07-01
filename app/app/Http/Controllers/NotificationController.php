<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Matching;

class NotificationController extends Controller
{
    /**
     * 未読通知数を取得
     */
    public function getNotificationCount(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        
        // 未読メッセージ数を取得
        $unreadMessageCount = $this->getUnreadMessageCount($user);
        
        // 未確認マッチング数を取得（pendingステータスのもの）
        $pendingMatchingCount = $this->getPendingMatchingCount($user);

        return response()->json([
            'unread_message_count' => $unreadMessageCount,
            'pending_matching_count' => $pendingMatchingCount,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * 未読メッセージ数を取得
     * read_atがnullのメッセージをカウント
     */
    private function getUnreadMessageCount($user)
    {
        return Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * 未確認マッチング数を取得
     * status = 0 (pending) のマッチングをカウント
     * matchingsテーブルにはreceiver_idがないため、
     * offering_skill_idまたはreceiving_skill_idから判断
     */
    private function getPendingMatchingCount($user)
    {
        // ユーザーのスキルIDを取得（この部分は実際のUserモデルの構造に応じて調整）
        $userSkillIds = $user->skills()->pluck('id')->toArray();
        
        return Matching::where('status', 0) // 0: pending
            ->where(function($query) use ($userSkillIds) {
                $query->whereIn('offering_skill_id', $userSkillIds)
                      ->orWhereIn('receiving_skill_id', $userSkillIds);
            })
            ->count();
    }

    /**
     * メッセージを既読にする
     */
    public function markMessageAsRead(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer|exists:messages,id'
        ]);

        $user = Auth::user();
        $messageId = $request->message_id;

        // 受信者が現在のユーザーであることを確認
        $message = Message::where('id', $messageId)
            ->where('receiver_id', $user->id)
            ->first();

        if ($message) {
            $message->update(['read_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'メッセージを既読にしました'
            ]);
        }

        return response()->json(['error' => 'Message not found'], 404);
    }

    /**
     * マッチングを確認済みにする（pendingからconfirmedへ）
     */
    public function confirmMatching(Request $request)
    {
        $request->validate([
            'matching_id' => 'required|integer|exists:matchings,id'
        ]);

        $user = Auth::user();
        $matchingId = $request->matching_id;

        // ユーザーのスキルIDを取得
        $userSkillIds = $user->skills()->pluck('id')->toArray();

        // ユーザーが関連するpendingステータスのマッチングを取得
        $matching = Matching::where('id', $matchingId)
            ->where('status', 0) // 0: pending
            ->where(function($query) use ($userSkillIds) {
                $query->whereIn('offering_skill_id', $userSkillIds)
                      ->orWhereIn('receiving_skill_id', $userSkillIds);
            })
            ->first();

        if ($matching) {
            $matching->update(['status' => 1]); // 1: confirmed
            
            return response()->json([
                'success' => true,
                'message' => 'マッチングを確認しました'
            ]);
        }

        return response()->json(['error' => 'Matching not found or already processed'], 404);
    }

    /**
     * マッチングをキャンセルする（pendingからcanceledへ）
     */
    public function cancelMatching(Request $request)
    {
        $request->validate([
            'matching_id' => 'required|integer|exists:matchings,id'
        ]);

        $user = Auth::user();
        $matchingId = $request->matching_id;

        // ユーザーのスキルIDを取得
        $userSkillIds = $user->skills()->pluck('id')->toArray();

        // ユーザーが関連するpendingステータスのマッチングを取得
        $matching = Matching::where('id', $matchingId)
            ->where('status', 0) // 0: pending
            ->where(function($query) use ($userSkillIds) {
                $query->whereIn('offering_skill_id', $userSkillIds)
                      ->orWhereIn('receiving_skill_id', $userSkillIds);
            })
            ->first();

        if ($matching) {
            $matching->update(['status' => 3]); // 3: canceled
            
            return response()->json([
                'success' => true,
                'message' => 'マッチングをキャンセルしました'
            ]);
        }

        return response()->json(['error' => 'Matching not found or already processed'], 404);
    }

    /**
     * マッチングを完了済みにする（confirmedからcompletedへ）
     */
    public function completeMatching(Request $request)
    {
        $request->validate([
            'matching_id' => 'required|integer|exists:matchings,id'
        ]);

        $user = Auth::user();
        $matchingId = $request->matching_id;

        // ユーザーのスキルIDを取得
        $userSkillIds = $user->skills()->pluck('id')->toArray();

        // ユーザーが関連するconfirmedステータスのマッチングを取得
        $matching = Matching::where('id', $matchingId)
            ->where('status', 1) // 1: confirmed
            ->where(function($query) use ($userSkillIds) {
                $query->whereIn('offering_skill_id', $userSkillIds)
                      ->orWhereIn('receiving_skill_id', $userSkillIds);
            })
            ->first();

        if ($matching) {
            $matching->update(['status' => 2]); // 2: completed
            
            return response()->json([
                'success' => true,
                'message' => 'マッチングを完了しました'
            ]);
        }

        return response()->json(['error' => 'Matching not found or not in confirmed status'], 404);
    }

    /**
     * ユーザーの全通知データを取得
     */
    public function getAllNotifications(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // 未読メッセージを取得
        $unreadMessages = Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->with('sender:id,name')
            ->orderBy('sent_at', 'desc')
            ->get();

        // ペンディング中のマッチングを取得
        $pendingMatchings = Matching::where('receiver_id', $user->id)
            ->where('status', 0)
            ->with(['sender:id,name', 'offeringSkill', 'receivingSkill'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'unread_messages' => $unreadMessages,
            'pending_matchings' => $pendingMatchings,
            'counts' => [
                'unread_messages' => $unreadMessages->count(),
                'pending_matchings' => $pendingMatchings->count()
            ]
        ]);
    }
}