<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Matching;
use App\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function form($matchingId)
    {
        $matching = Matching::with(['offeringSkill', 'receivingSkill'])->findOrFail($matchingId);
        $userId = Auth::id();

        if ($matching->status !== 2) {
            abort(403, '完了していないマッチングにはレビューできません');
        }

        // 自分が参加しているマッチングか？
        if (
            $matching->offeringSkill->user_id !== $userId &&
            $matching->receivingSkill->user_id !== $userId
        ) {
            abort(403);
        }

        // 既にレビュー済みか？
        $alreadyReviewed = Review::where('matching_id', $matchingId)
            ->where('reviewer_id', $userId)
            ->exists();

        if ($alreadyReviewed) {
            return redirect('/matching/history')->with('message', 'すでにレビュー済みです。');
        }

        return view('review.review', ['matching' => $matching]);
    }

    public function submit(Request $request, $matchingId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $matching = Matching::with(['offeringSkill', 'receivingSkill'])->findOrFail($matchingId);
        $userId = Auth::id();

        // レビュー対象ユーザーを決定
        $revieweeId = ($matching->offeringSkill->user_id === $userId)
            ? $matching->receivingSkill->user_id
            : $matching->offeringSkill->user_id;

        Review::create([
            'matching_id' => $matchingId,
            'reviewer_id' => $userId,
            'reviewee_id' => $revieweeId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect('/matching/history')->with('message', 'レビューを投稿しました。');
    }
}
