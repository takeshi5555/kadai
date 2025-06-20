<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Logファサードを使用
use App\Models\Skill;
use App\Models\User;
use App\Models\Matching;
use App\Models\Review;

class SkillController extends Controller
{
    /**
     * スキル検索結果を表示
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
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

        // スキルを最新順に取得（ページネーションが必要であれば`paginate()`を使用）
        $skills = $query->latest()->get();

        // Skillテーブルからユニークなカテゴリの一覧を取得
        $categories = Skill::select('category')->distinct()->pluck('category')->toArray(); // Collectionから配列に変換

        // ★★★ ここから「その他」カテゴリの処理を追加 ★★★
        $otherCategory = null;
        $filteredCategories = [];
        foreach ($categories as $cat) {
            if ($cat === 'その他') {
                $otherCategory = $cat;
            } else {
                $filteredCategories[] = $cat;
            }
        }
        // 「その他」を除いたカテゴリをソート
        sort($filteredCategories); 

        // 「その他」があれば、最後に結合する
        if ($otherCategory !== null) {
            $filteredCategories[] = $otherCategory;
        }
        $categories = collect($filteredCategories); // 再びCollectionに変換してビューに渡す
        // ★★★ 「その他」カテゴリの処理ここまで ★★★

        // ビューにデータを渡して表示
        return view('user.skill_search', [
            'skills' => $skills,
            'categories' => $categories,
        ]);
    }

    /**
     * スキルの詳細を表示
     *
     * @param int $id スキルID
     * @return \Illuminate\View\View
     */
   public function show($id)
    {
        $skill = Skill::with('user')->findOrFail($id);

        // --- このスキルが関連するマッチング件数 ---
        $offeredSkillMatchingsCount = $skill->offeredMatchings()->whereIn('status', [1, 2])->count();
        $receivedSkillMatchingsCount = $skill->receivedMatchings()->whereIn('status', [1, 2])->count();
        $skillMatchingCount = $offeredSkillMatchingsCount + $receivedSkillMatchingsCount;

        // --- スキル提供者（ユーザー）の情報 ---
        $user = $skill->user;

        $userTotalOfferedMatchingsCount = $user->offeredMatchings()->whereIn('status', [1, 2])->count();
        $userTotalReceivedMatchingsCount = $user->receivedMatchings()->whereIn('status', [1, 2])->count();
        $userTotalMatchingCount = $userTotalOfferedMatchingsCount + $userTotalReceivedMatchingsCount;

        // 提供者の全レビューの評価平均
        $userAverageRating = $user->reviewsReceived()->avg('rating');

        // --- スキルへのレビュー取得と評価平均 ---
        $offeredMatchingIds = $skill->offeredMatchings()->pluck('id');
        $receivedMatchingIds = $skill->receivedMatchings()->pluck('id');
        $relevantMatchingIds = $offeredMatchingIds->merge($receivedMatchingIds)->unique();

        $skillReviews = Review::whereIn('matching_id', $relevantMatchingIds)
                              ->where('reviewee_id', $skill->user->id)
                              ->with('reviewerUser')
                              ->latest()
                              ->get();
        $skillAverageRating = $skillReviews->avg('rating');

        // ★★★ ここに提供者の他のスキルを取得するロジックを追加 ★★★
        // 現在のスキル以外の、この提供者が提供しているスキルを最大3件取得する例
        $otherUserSkills = $user->skills()
                                ->where('id', '!=', $skill->id) // 現在表示中のスキルを除外
                                ->latest() // 最新のスキルから取得
                                ->limit(3) // 最大3件に制限
                                ->get();

        return view('user.skill_detail', [
            'skill' => $skill,
            'skillMatchingCount' => $skillMatchingCount,
            'userTotalMatchingCount' => $userTotalMatchingCount,
            'userAverageRating' => $userAverageRating,
            'skillReviews' => $skillReviews,
            'skillAverageRating' => $skillAverageRating,
            'otherUserSkills' => $otherUserSkills, // ★この行を追加★
        ]);
    }


    /**
     * スキル管理ページを表示
     *
     * @return \Illuminate\View\View
     */
    public function manage()
    {
        $user = Auth::user();
        $skills = $user->skills()->latest()->get(); // ユーザーのスキルを最新順に取得

        // Skillテーブルからユニークなカテゴリ名を取得し、ソート
        $categories = Skill::select('category')->distinct()->pluck('category')->toArray(); // Collectionから配列に変換

        // ★★★ ここから「その他」カテゴリの処理を追加 ★★★
        $otherCategory = null;
        $filteredCategories = [];
        foreach ($categories as $cat) {
            if ($cat === 'その他') {
                $otherCategory = $cat;
            } else {
                $filteredCategories[] = $cat;
            }
        }
        // 「その他」を除いたカテゴリをソート
        sort($filteredCategories); 

        // 「その他」があれば、最後に結合する
        if ($otherCategory !== null) {
            $filteredCategories[] = $otherCategory;
        }
        $categories = collect($filteredCategories); // 再びCollectionに変換してビューに渡す
        // ★★★ 「その他」カテゴリの処理ここまで ★★★

        return view('user.skill_manage', compact('skills', 'categories'));
    }

    /**
     * 新しいスキルを保存
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // バリデーションルール
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100', 
            'description' => 'required|string',
            'skill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 画像のバリデーション
        ]);

        $imagePath = null;
        // 画像がアップロードされた場合
        if ($request->hasFile('skill_image')) {
            try {
                // 画像をストレージに保存
                $imagePath = $request->file('skill_image')->store('skill_images', 'public');
            } catch (\Exception $e) {
                // ファイル保存エラーをログに出力
                Log::error('ファイル保存エラー: ' . $e->getMessage());
                // エラーメッセージと共にフォームに戻る
                return back()->withErrors(['skill_image' => '画像のアップロードに失敗しました。時間をおいて再度お試しください。'])->withInput();
            }
        }

        // スキルを作成
        Auth::user()->skills()->create([
            'title' => $validatedData['title'],
            'category' => $validatedData['category'],
            'description' => $validatedData['description'],
            'image_path' => $imagePath, // 画像パスを保存
        ]);

        return redirect('/skill/manage')->with('message', 'スキルを登録しました。');
    }

    /**
     * 既存のスキルを更新
     *
     * @param Request $request
     * @param int $id スキルID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $skill = Skill::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        try {
            $rules = [
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'description' => 'required|string',
                'skill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];

            $validatedData = $request->validate($rules);

            $imagePath = $skill->image_path; // 現在の画像パスを保持

            // ★★★ ここから追加 ★★★
            // 「画像を削除する」チェックボックスがオンの場合
            if ($request->has('clear_image') && $request->input('clear_image') === 'on') {
                if ($skill->image_path) {
                    Storage::disk('public')->delete($skill->image_path); // 古い画像を削除
                }
                $imagePath = null; // データベースのパスをnullに設定
            }
            // ★★★ ここまで追加 ★★★
            
            // 新しい画像がアップロードされた場合（「画像を削除する」より優先）
            if ($request->hasFile('skill_image')) {
                // 古い画像があればストレージから削除（削除チェックボックスより優先）
                if ($skill->image_path && $skill->image_path !== $imagePath) { // imagePathがnullになってないか確認して削除
                    Storage::disk('public')->delete($skill->image_path);
                }
                $imagePath = $request->file('skill_image')->store('skill_images', 'public');
            }


            $skill->update([
                'title' => $validatedData['title'],
                'category' => $validatedData['category'],
                'description' => $validatedData['description'],
                'image_path' => $imagePath, // 更新されたパス（nullの場合も含む）を保存
            ]);

            $updatedSkill = $skill->fresh();

            // フロントエンドが期待する画像URL形式に変換
            if ($updatedSkill->image_path && !str_starts_with($updatedSkill->image_path, 'http')) {
                $updatedSkill->image_path = Storage::url($updatedSkill->image_path);
            }
            // 画像パスがnullの場合、空文字列を返す（JavaScriptの処理に合わせるため）
            if ($updatedSkill->image_path === null) {
                $updatedSkill->image_path = '';
            }

            return response()->json([
                'success' => true,
                'message' => 'スキルが正常に更新されました。',
                'skill' => $updatedSkill->toArray()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Skill update validation failed for user ' . Auth::id() . ': ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => '入力内容に問題があります。',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Skill update failed unexpectedly for user ' . Auth::id() . ': ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'スキルの更新に失敗しました。サーバーで予期せぬエラーが発生しました。',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * スキルを削除
     *
     * @param int $id スキルID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // ログイン中のユーザーが所有するスキルであることを確認
        $skill = Skill::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // 関連する画像ファイルがあればストレージから削除
        if ($skill->image_path) {
            Storage::disk('public')->delete($skill->image_path);
        }

        // スキルを削除
        $skill->delete();

        return redirect('/skill/manage')->with('message', 'スキルを削除しました。');
    }
}