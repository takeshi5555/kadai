<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;

class SkillsController extends Controller
{
    public function index(Request $request)
    {
        $query = Skill::with('user'); 

        // 検索機能
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        // 特定のユーザーIDでフィルタリング
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // ★★★ ここからソート機能の追加 ★★★
        $sortColumn = $request->input('sort', 'created_at'); // デフォルトはcreated_at
        $sortDirection = $request->input('direction', 'desc'); // デフォルトはdesc

        // ソート可能なカラムをホワイトリストで定義
        $allowedSortColumns = ['id', 'title', 'created_at', 'user_id']; // 'title' も追加しました

        // ソートカラムが許可されているか確認
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at'; // 許可されていない場合はデフォルトに戻す
        }

        // ソート方向が有効か確認
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc'; // 無効な場合はデフォルトに戻す
        }
        
        // 所有者名でソートしたい場合 (user_id ではなく user.name)
        // もし user.name でソートしたいなら、JOINが必要になります。
        // 例:
        if ($sortColumn === 'user_id') {
             $query->leftJoin('users', 'skills.user_id', '=', 'users.id')
                   ->orderBy('users.name', $sortDirection)
                   ->select('skills.*'); // select を指定しないとusersテーブルの重複したカラム名でエラーになる可能性があります
        } else {
            // 通常のソート
            $query->orderBy($sortColumn, $sortDirection);
        }

        // 同じ created_at 値の場合にIDで二次ソート（デフォルトソート時も有効）
        // ただし、上記でuser_idソート時にleftJoinしているため、
        // created_at や id のソートは最後に実行されるようにする必要があります。
        // ソートの順序は重要です。
        if ($sortColumn !== 'created_at' && $sortColumn !== 'id') {
            // 現在ソート中のカラムが created_at や id でない場合のみ、デフォルトソートとして追加
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        // ★★★ ソート機能の追加ここまで ★★★

        $skills = $query->paginate(10);
        
        return view('admin.skills.index', compact('skills'));
    }

    public function edit(Skill $skill)
    {
        return view('admin.skills.edit', compact('skill'));
    }

    public function update(Request $request, Skill $skill)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $skill->title = $request->input('title');
        $skill->description = $request->input('description');
        $skill->save();

        return redirect()->route('admin.skills.index')->with('success', 'スキル情報が更新されました。');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return redirect()->route('admin.skills.index')->with('success', 'スキルが削除されました。');
    }
}