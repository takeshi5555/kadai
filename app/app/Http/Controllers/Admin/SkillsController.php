<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\User; // Userモデルが使われているか確認
use Illuminate\Http\Request;

class SkillsController extends Controller
{
    public function index(Request $request)
    {
        // まず、Skillモデルのクエリビルダーを開始し、userリレーションをロード
        // user_id でソートする場合に備えて、join を適用する前に with() を呼び出しておく
        $query = Skill::with('user'); 

        // 検索機能
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // カテゴリでの絞り込み機能を追加
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // 特定のユーザーIDでフィルタリング
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // --- ソート機能 ---
        $sortColumn = $request->input('sort', 'created_at'); // デフォルトはcreated_at
        $sortDirection = $request->input('direction', 'desc'); // デフォルトはdesc

        // ソート可能なカラムをホワイトリストで定義
        // 'category' を追加
        // 'user.name' は特別な処理が必要なので、ここでは 'user_id' として扱う
        $allowedSortColumns = ['id', 'title', 'category', 'created_at', 'user_id']; 

        // ソートカラムが許可されているか確認
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at'; // 許可されていない場合はデフォルトに戻す
        }

        // ソート方向が有効か確認
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc'; // 無効な場合はデフォルトに戻す
        }
        
        // user.name でソートする場合の特別な処理
        if ($sortColumn === 'user_id') {
            // usersテーブルをleftJoinし、ユーザー名でソート
            // select('skills.*') でskillsテーブルの全カラムを選択し、カラム名衝突を避ける
            $query->leftJoin('users', 'skills.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection)
                  ->select('skills.*'); 
        } else {
            // その他のカラムでのソート
            $query->orderBy($sortColumn, $sortDirection);
        }

        // メインのソート条件が適用された後、同一値の場合の二次ソートを設定
        // 例えば、titleでソートした場合、同じtitleのスキルはcreated_atとidでソートされる
        if ($sortColumn !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }
        if ($sortColumn !== 'id') {
            $query->orderBy('id', 'desc');
        }
        // ここで既に created_at と id でデフォルトのソートが設定されているため、
        // 最初の orderBy は省略しても良いかもしれませんが、明確にするために残します。
        // $query->orderBy($sortColumn, $sortDirection); は既に上記で適用されているため、
        // この後の created_at, id のorderByはあくまで「二次ソート」として機能します。

        // --- ソート機能ここまで ---

        // ページネーションを適用
        $skills = $query->paginate(10);
        
        // ★★★ ここを追加 ★★★
        // カテゴリ絞り込みフォーム用に、ユニークなカテゴリ一覧を取得
        $categories = Skill::select('category')->distinct()->pluck('category')->sort()->values();

        // ビューにデータを渡す
        return view('admin.skills.index', compact('skills', 'categories'));
    }

    /**
     * スキル編集フォームを表示
     *
     * @param \App\Models\Skill $skill
     * @return \Illuminate\View\View
     */
    public function edit(Skill $skill)
    {
        // カテゴリ編集モーダル用に、ユニークなカテゴリ一覧を取得
        $categories = Skill::select('category')->distinct()->pluck('category')->sort()->values();
        return view('admin.skills.edit', compact('skill', 'categories'));
    }

    /**
     * スキルを更新
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Skill $skill
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Skill $skill)
    {
        // バリデーションルールにcategoryを追加
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100', // categoryのバリデーションを追加
            'description' => 'nullable|string|max:1000',
        ]);

        $skill->title = $request->input('title');
        $skill->category = $request->input('category'); // categoryの値を更新
        $skill->description = $request->input('description');
        $skill->save();

        return redirect()->route('admin.skills.index')->with('success', 'スキル情報が更新されました。');
    }

    /**
     * スキルを削除
     *
     * @param \App\Models\Skill $skill
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Skill $skill)
    {
        $skill->delete();
        return redirect()->route('admin.skills.index')->with('success', 'スキルが削除されました。');
    }
}