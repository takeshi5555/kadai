<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Matching;
use App\Message;
use App\Models\User;
use App\Events\MessageSent; // これを追加
use Illuminate\Support\Facades\Log; // 必要に応じてログのため

class MessageController extends Controller
{
    // メッセージ履歴表示
    public function show($matchingId)
    {
        // マッチング情報を取得（リレーションを事前にロード）
        $matching = Matching::with(['offeringSkill.user', 'receivingSkill.user'])->findOrFail($matchingId);
        $currentUserId = Auth::id();

        // ユーザーがマッチングの当事者でなければアクセス拒否
        if (
            $matching->offeringSkill->user_id !== $currentUserId &&
            $matching->receivingSkill->user_id !== $currentUserId
        ) {
            abort(403, 'このマッチングのメッセージにアクセスする権限がありません。');
        }

        // マッチングに関連するメッセージを取得し、日付順にソート
        $messages = Message::where('matching_id', $matchingId)
                            ->with('sender') // 送信者情報をロード
                            ->orderBy('created_at') // created_at でソート
                            ->get();

        return view('message.message', compact('matching', 'messages'));
    }

    // メッセージ送信
    public function store(Request $request, $matchingId)
    {
        $request->validate(['content' => 'required|string|max:1000']);

        $matching = Matching::with(['offeringSkill.user', 'receivingSkill.user'])->findOrFail($matchingId);
        $currentUserId = Auth::id();

        // 権限チェック：マッチングに関与しているユーザーのみがメッセージを送信できる
        if ($matching->offeringSkill->user_id !== $currentUserId && $matching->receivingSkill->user_id !== $currentUserId) {
            abort(403, 'このマッチングにメッセージを送信する権限がありません。');
        }

        $receiverId = null;
        // 受信者のIDを決定するロジック
        if ($matching->offeringSkill->user_id === $currentUserId) {
            $receiverId = $matching->receivingSkill->user_id;
        } else {
            $receiverId = $matching->offeringSkill->user_id;
        }

        if (is_null($receiverId)) {
            Log::error("Failed to determine receiverId for matching ID: {$matchingId}");
            return response()->json(['error' => 'メッセージの送信相手を特定できませんでした。'], 500);
        }

        $message = Message::create([
            'matching_id' => $matchingId,
            'sender_id' => $currentUserId,
            'receiver_id' => $receiverId,
            'content' => $request->content,
            'sent_at' => now(), // もしsent_atを使うなら
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // イベントを発火 (新しいメッセージと送信ユーザー)
        broadcast(new MessageSent($message, Auth::user()))->toOthers(); // toOthers() で自分以外にブロードキャスト

        return response()->json(['message' => 'メッセージを送信しました。']);
    }
}