<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }
    
    
    public function edit(User $user)
    {
        // 編集フォームにロールの選択肢を渡す場合はここで定義
        $roles = ['user', 'moderator', 'admin']; // 定義済みのロール
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'moderator', 'admin'])], // ロールを追加
        ]);

        // 自分自身のロールを変更できないようにする (またはadmin権限を剥奪できないようにする)
        if ($user->id === auth()->id() && $request->input('role') !== $user->role && !$user->isAdmin()) {
            return back()->with('error', '自分自身のロールを変更することはできません。');
        }
        // 管理者自身が自分のadminロールを剥奪できないようにする
        if ($user->id === auth()->id() && $user->isAdmin() && $request->input('role') !== 'admin') {
             return back()->with('error', '管理者自身は自身の管理者権限を剥奪できません。');
        }


        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->role = $request->input('role'); // ★ロールを更新
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'ユーザー情報が更新されました。');
    }


    public function destroy(User $user)
    {
        // 自分自身を削除できないようにするなどのロジックを追加することも可能
        if ($user->id === auth()->id()) {
            return back()->with('error', '自分自身を削除することはできません。');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'ユーザーが削除されました。');
    }
}
