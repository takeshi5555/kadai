<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skill;
use App\Models\User;
use App\Models\Matching; 
use App\Models\Review; 

class SkillController extends Controller
{
        public function search(Request $request)
    {
        // 検索クエリを初期化
        $query = Skill::query();

        // ログイン中のユーザーIDを取得
        // ユーザーがログインしているかを確認し、ログインしていなければnullを設定
        $userId = Auth::id(); // Auth::id() はログインしていなければ null を返すため、このままでOK

        // キーワード検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword'); // input() メソッドを使うと安全
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // カテゴリ絞り込み
        if ($request->filled('category')) {
            $query->where('category', $request->input('category')); // input() メソッドを使うと安全
        }

        // 自分のスキルを除外するロジック
        // ユーザーがログインしている場合のみ、自分のスキルを除外
        if ($userId) {
            $query->where('user_id', '!=', $userId);
        }

        // スキルを最新順に取得
        $skills = $query->latest()->get();

        // Skillテーブルからユニークなカテゴリの一覧を取得
        $categories = Skill::select('category')->distinct()->pluck('category');

        // ビューにデータを渡して表示
        return view('user.skill_search', [
            'skills' => $skills,
            'categories' => $categories,
        ]);
    }

    public function show($id)
    {
        // スキルと提供者情報を取得
        $skill = Skill::with('user')->findOrFail($id);

        // --- このスキルが関連するマッチング件数 ---
        // このスキルが提供される側として確定/完了したマッチング数
        $offeredSkillMatchingsCount = $skill->offeredMatchings()->whereIn('status', [1, 2])->count();
        // このスキルが受け取られる側として確定/完了したマッチング数
        $receivedSkillMatchingsCount = $skill->receivedMatchings()->whereIn('status', [1, 2])->count();
        $skillMatchingCount = $offeredSkillMatchingsCount + $receivedSkillMatchingsCount;


        // --- スキル提供者（ユーザー）の情報 ---
        $user = $skill->user; // スキル提供者のユーザー情報

        // 提供者の全マッチング件数 (提供・受領の両方)
        $userTotalOfferedMatchingsCount = $user->offeredMatchings()->whereIn('status', [1, 2])->count();
        $userTotalReceivedMatchingsCount = $user->receivedMatchings()->whereIn('status', [1, 2])->count();
        $userTotalMatchingCount = $userTotalOfferedMatchingsCount + $userTotalReceivedMatchingsCount;

        // 提供者の全レビューの評価平均 (reviewee_id がこのユーザーであるレビューの平均)
        $userAverageRating = $user->reviewsReceived()->avg('rating');

        // 提供者に対する最新レビュー (reviewee_id がこのユーザーであるレビューの最新3件)
        $userLatestReviews = $user->reviewsReceived()->latest()->limit(3)->get();


        return view('user.skill_detail', [
            'skill' => $skill,
            'skillMatchingCount' => $skillMatchingCount,
            'userTotalMatchingCount' => $userTotalMatchingCount,
            'userAverageRating' => $userAverageRating,
            'userLatestReviews' => $userLatestReviews, // 提供者への最新レビュー
        ]);
    }

    
    // スキル管理ページ
    public function manage()
    {
        $skills = Skill::where('user_id', Auth::id())->get();
        return view('user.skill_manage', compact('skills'));
    }

    // スキル登録
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
        ]);

        Skill::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
        ]);

        return redirect('/skill/manage')->with('message', 'スキルを登録しました。');
    }

    // 編集フォーム表示
    public function edit($id)
    {
        $skill = Skill::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('user.skill_edit', compact('skill'));
    }

    // 更新処理
    public function update(Request $request, $id)
    {
        $skill = Skill::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $skill->update([
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
        ]);

        return redirect('/skill/manage')->with('message', 'スキルを更新しました。');
    }

    // 削除処理
    public function destroy($id)
    {
        $skill = Skill::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $skill->delete();

        return redirect('/skill/manage')->with('message', 'スキルを削除しました。');
    }

    
}
