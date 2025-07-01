<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Matching;
use App\Models\Message;
use App\Events\MessageSent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // 必要に応じてログのため
use App\Models\User;
use App\Notifications\MessageReceivedNotification;

class MessageController extends Controller
{
    public function show($matchingId)
    {
        $matching = Matching::with(['offerUser', 'requestUser', 'messages.sender'])->findOrFail($matchingId);
        $currentUserId = Auth::id();

        if (
            ($matching->offerUser->id !== $currentUserId) &&
            ($matching->requestUser->id !== $currentUserId)
        ) {
            abort(403, 'このマッチングのメッセージにアクセスする権限がありません。');
        }

        Message::where('matching_id', $matchingId)
               ->where('receiver_id', $currentUserId)
               ->whereNull('read_at')
               ->update(['read_at' => Carbon::now()]);

        $messages = Message::where('matching_id', $matchingId)
                            ->with('sender')
                            ->orderBy('created_at')
                            ->get();

        return view('message.message', compact('matching', 'messages'));
    }

    public function store(Request $request, $matchingId)
    {
        $request->validate(['content' => 'required|string|max:1000']);

        $matching = Matching::with(['offerUser', 'requestUser'])->findOrFail($matchingId);
        $currentUserId = Auth::id();

        if (
            ($matching->offerUser->id !== $currentUserId) &&
            ($matching->requestUser->id !== $currentUserId)
        ) {
            abort(403, 'このマッチングにメッセージを送信する権限がありません。');
        }

        $receiverId = ($matching->offerUser->id === $currentUserId) ? $matching->requestUser->id : $matching->offerUser->id;

        if (is_null($receiverId)) {
            Log::error("Failed to determine receiverId for matching ID: {$matchingId}");
            return response()->json(['error' => 'メッセージの送信相手を特定できませんでした。'], 500);
        }

        $message = Message::create([
            'matching_id' => $matchingId,
            'sender_id' => $currentUserId,
            'receiver_id' => $receiverId,
            'content' => $request->content,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        
try {
    $receiver = User::find($receiverId);
    if ($receiver) {
        $receiver->notify(new MessageReceivedNotification($message));
    }
} catch (\Throwable $e) {
    Log::error('通知送信エラー: ' . $e->getMessage());
    // 通知失敗してもメッセージ送信自体は止めない
}

        broadcast(new MessageSent($message, Auth::user()))->toOthers();

        return response()->json(['message' => 'メッセージを送信しました。']);
    }
}