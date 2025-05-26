<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Skill;
use App\Matching; 
use App\Message; 
use App\User;

// app/Http/Controllers/MessageController.php
class MessageController extends Controller
{
    public function show($matchingId)
    {
        $matching = Matching::findOrFail($matchingId);

        // マッチングに参加しているユーザー（offeringSkillのユーザーとreceivingSkillのユーザー）のみがメッセージを見れるようにする
        $currentUserId = Auth::id();
        if (
            $matching->offeringSkill->user_id !== $currentUserId &&
            $matching->receivingSkill->user_id !== $currentUserId
        ) {
            abort(403, 'このマッチングのメッセージを閲覧する権限がありません。');
        }

        $messages = Message::where('matching_id', $matchingId) // 変数名をmessagesに統一
            ->with('sender')
            ->orderBy('sent_at')
            ->get();

        return view('message.message', [
            'matching' => $matching,
            'messages' => $messages // 変数名をmessagesに統一
        ]);
    }

    public function store(Request $request, $matchingId)
    {
        $request->validate(['content' => 'required|string|max:1000']);

        $matching = Matching::findOrFail($matchingId); // マッチングの存在を確認し、権限チェックのため取得

        // マッチングに参加しているユーザーのみがメッセージを送信できるようにする
        $currentUserId = Auth::id();
        if (
            $matching->offeringSkill->user_id !== $currentUserId &&
            $matching->receivingSkill->user_id !== $currentUserId
        ) {
            abort(403, 'このマッチングにメッセージを送信する権限がありません。');
        }
         $receiverId = ($matching->offeringSkill->user_id === $currentUserId)
        ? $matching->receivingSkill->user_id
        : $matching->offeringSkill->user_id; // ここで$receiverIdが定義される

        Message::create([
            'matching_id' => $matchingId,
            'sender_id' => auth()->id(),
            'receiver_id' => $receiverId,
            'content' => $request->content,
            'sent_at' => now(),
        ]);

        return redirect()->back();
    }
}
