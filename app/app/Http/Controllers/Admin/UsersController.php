<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate; // Gate を使うために追加

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // 検索機能
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        // ソート機能
        $sortField = $request->input('sort', 'created_at'); // デフォルトは登録日
        $sortDirection = $request->input('direction', 'desc'); // デフォルトは降順

        // ソート可能なフィールドを制限（セキュリティ対策）
        $allowedSortFields = ['id', 'name', 'email', 'role', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // ソート方向を制限
        $allowedDirections = ['asc', 'desc'];
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        $users = $query->orderBy($sortField, $sortDirection)->paginate(10);
        
        // ページネーション時にパラメータを保持
        $users->appends($request->query());

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


    /**
     * ユーザーのBAN状態を切り替える
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function toggleBan(User $user)
    {
        // Gate を使うために Illuminate\Support\Facades\Gate を use すること
        // Gateで認可チェックを行う
        // can-ban-users Gateを定義した場合 (モデレーターもBANできる場合)
        Gate::authorize('can-ban-users'); // 引数に$userを渡すと、Policyに渡される
        // もし、access-admin-only-sections Gateを使う場合（前のターンでこのGateをモデレーターにも許可した場合）
        // Gate::authorize('access-admin-only-sections');

        // BAN状態を反転させる
        $user->is_banned = !$user->is_banned;
        $user->save();

        // 適切なリダイレクトを返す
        return back()->with('status', $user->name . ' さんのBAN状態を切り替えました。');
    }
}