<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        // 未読メッセージ数を取得（実装に応じて調整）
        $unreadMessageCount = $this->getUnreadMessageCount($user);
        
        // 未確認マッチング数を取得（実装に応じて調整）
        $unconfirmedMatchingCount = $this->getUnconfirmedMatchingCount($user);

        return response()->json([
            'unread_message_count' => $unreadMessageCount,
            'unconfirmed_matching_count' => $unconfirmedMatchingCount,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * 未読メッセージ数を取得
     */
    private function getUnreadMessageCount($user)
    {
        // 実際の実装に応じて調整してください
        // 例：メッセージテーブルから未読メッセージをカウント
        return $user->receivedMessages()
            ->where('is_read', false)
            ->count();
        
        // または、既存のunread_message_countアクセサを使用
        // return $user->unread_message_count;
    }

    /**
     * 未確認マッチング数を取得
     */
    private function getUnconfirmedMatchingCount($user)
    {
        // 実際の実装に応じて調整してください
        // 例：マッチングテーブルから未確認マッチングをカウント
        return $user->receivedMatchings()
            ->where('is_confirmed', false)
            ->count();
        
        // または、既存のunconfirmed_matching_countアクセサを使用
        // return $user->unconfirmed_matching_count;
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

        // メッセージを既読にする処理
        $message = $user->receivedMessages()->find($messageId);
        if ($message) {
            $message->update(['is_read' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'メッセージを既読にしました'
            ]);
        }

        return response()->json(['error' => 'Message not found'], 404);
    }

    /**
     * マッチングを確認済みにする
     */
    public function confirmMatching(Request $request)
    {
        $request->validate([
            'matching_id' => 'required|integer|exists:matchings,id'
        ]);

        $user = Auth::user();
        $matchingId = $request->matching_id;

        // マッチングを確認済みにする処理
        $matching = $user->receivedMatchings()->find($matchingId);
        if ($matching) {
            $matching->update(['is_confirmed' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'マッチングを確認しました'
            ]);
        }

        return response()->json(['error' => 'Matching not found'], 404);
    }
}