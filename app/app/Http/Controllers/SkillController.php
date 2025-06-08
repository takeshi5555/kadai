<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // ★ 追加
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
        $userId = Auth::id();

        // キーワード検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // カテゴリ絞り込み
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // 自分のスキルを除外するロジック
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



    public function store(Request $request)
    {

       
        $validatedData = $request->validate([ // ★ バリデーションルールを修正
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'skill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 画像のバリデーションを追加
        ]);

            $imagePath = null;
    if ($request->hasFile('skill_image')) {
        try {
            // ここにdd() を置いてみる
            $imagePath = $request->file('skill_image')->store('skill_images', 'public');
            
        } catch (\Exception $e) {
           \Log::error('ファイル保存エラー: ' . $e->getMessage());
        }
    }

        Skill::create([
            'user_id' => Auth::id(),
            'title' => $validatedData['title'], // ★ $validatedData を使用
            'category' => $validatedData['category'], // ★ $validatedData を使用
            'description' => $validatedData['description'], // ★ $validatedData を使用
            'image_path' => $imagePath, // ★ image_path を保存
        ]);

        return redirect('/skill/manage')->with('message', 'スキルを登録しました。');
    }




    // 更新処理
    public function update(Request $request, $id)
    {
        $skill = Skill::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $validatedData = $request->validate([ // ★ バリデーションルールを修正
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'skill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 画像のバリデーションを追加
        ]);

        $imagePath = $skill->image_path; // 現在の画像パスを保持

        // 新しい画像がアップロードされた場合
        if ($request->hasFile('skill_image')) {
            // 古い画像があれば削除
            if ($skill->image_path) {
                Storage::disk('public')->delete($skill->image_path);
            }
           
            $imagePath = $request->file('skill_image')->store('skill_images', 'public');
        }

        $skill->update([
            'title' => $validatedData['title'],
            'category' => $validatedData['category'],
            'description' => $validatedData['description'],
            'image_path' => $imagePath, // 更新されたパスを保存
        ]);

        return redirect('/skill/manage')->with('message', 'スキルを更新しました。');
    }

    // 削除処理
    public function destroy($id)
    {
        $skill = Skill::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // 関連する画像ファイルがあれば削除
        if ($skill->image_path) {
            Storage::disk('public')->delete($skill->image_path);
        }

        $skill->delete();

        return redirect('/skill/manage')->with('message', 'スキルを削除しました。');
    }
}