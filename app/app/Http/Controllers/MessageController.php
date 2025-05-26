<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Skill;

// app/Http/Controllers/MessageController.php
class MessageController extends Controller
{
    public function show($matchingId)
    {
        $matching = Matching::findOrFail($matchingId);
        $content = Message::where('matching_id', $matchingId)
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        return view('message.message', [
            'matching' => $matching,
            'content' => $content
        ]);
    }

public function store(Request $request, $matchingId)
{
    $request->validate(['content' => 'required|string|max:1000']);

    Message::create([
        'matching_id' => $matchingId,
        'sender_id' => auth()->id(),
        'content' => $request->content,
    ]);

    return redirect()->back();
}

}
