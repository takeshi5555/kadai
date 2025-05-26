<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Skill;



class SkillController extends Controller
{
    public function index(Request $request)
    {
         $query = Skill::query();

        // ログイン中のユーザーIDを取得
        // ユーザーがログインしているかを確認し、ログインしていなければnullを設定
        $userId = Auth::check() ? Auth::id() : null;

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('description', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // --- 自分のスキルを除外するロジックをここに追加 ---
        // ユーザーがログインしている場合のみ、自分のスキルを除外
        if ($userId) {
            $query->where('user_id', '!=', $userId);
        }
        // --- ここまで ---

        $skills = $query->latest()->get();

        return view('user.skill_search', compact('skills'));
    }


    public function show($id)
    {
        $skill = Skill::findOrFail($id);

        return view('user.skill_detail', compact('skill'));
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
