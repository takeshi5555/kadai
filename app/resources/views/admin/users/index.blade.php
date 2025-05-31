@extends('layouts.admin')

@section('title', 'ユーザー管理')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">ユーザー管理</h1>

    {{-- フラッシュメッセージ --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            ユーザー一覧
        </div>
        <div class="card-body">
            {{-- 検索フォーム --}}
            <form action="{{ route('admin.users.index') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="名前またはメールアドレスで検索" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary ms-2" type="submit">検索</button>
                    @if(request('search'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-danger">クリア</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名前</th>
                            <th>メールアドレス</th>
                            <th>ロール</th>
                            <th>登録日</th>
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge {{ $user->role === 'admin' ? 'bg-danger' : ($user->role === 'moderator' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ ucfirst($user->role) }}</span></td>
                                <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    {{-- 編集ボタンをモーダル起動用に変更 --}}
                                    <button type="button" class="btn btn-sm btn-info text-white me-1 edit-user-btn"
                                            data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->role }}">
                                        編集
                                    </button>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline-block" onsubmit="return confirm('本当にこのユーザーを削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">ユーザーが見つかりません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーションリンク --}}
            <div class="d-flex justify-content-center">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ユーザー編集モーダル --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">ユーザー編集</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalUserName" class="form-label">名前</label>
                        <input type="text" class="form-control" id="modalUserName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalUserEmail" class="form-label">メールアドレス</label>
                        <input type="email" class="form-control" id="modalUserEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalUserRole" class="form-label">ロール</label>
                        <select class="form-select" id="modalUserRole" name="role" required>
                            <option value="user">User</option>
                            <option value="moderator">Moderator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    {{-- パスワード変更も可能にする場合は、ここにパスワードフィールドを追加 --}}
                    {{-- <div class="mb-3">
                        <label for="modalUserPassword" class="form-label">新しいパスワード (変更する場合のみ)</label>
                        <input type="password" class="form-control" id="modalUserPassword" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="modalUserPasswordConfirmation" class="form-label">新しいパスワード (確認)</label>
                        <input type="password" class="form-control" id="modalUserPasswordConfirmation" name="password_confirmation">
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">更新</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', function (event) {
        // モーダルを開くボタン
        const button = event.relatedTarget;

        // データ属性からユーザー情報を取得
        const userId = button.getAttribute('data-id');
        const userName = button.getAttribute('data-name');
        const userEmail = button.getAttribute('data-email');
        const userRole = button.getAttribute('data-role');

        // モーダル内のフォーム要素を取得
        const modalForm = editUserModal.querySelector('#editUserForm');
        const modalNameInput = editUserModal.querySelector('#modalUserName');
        const modalEmailInput = editUserModal.querySelector('#modalUserEmail');
        const modalRoleSelect = editUserModal.querySelector('#modalUserRole');

        // フォームのアクションURLを設定
        // 例: /admin/users/{id}
        modalForm.action = `/admin/users/${userId}`;

        // フォームにユーザー情報を設定
        modalNameInput.value = userName;
        modalEmailInput.value = userEmail;
        modalRoleSelect.value = userRole; // selectボックスの値を設定
    });

    // モーダルが閉じられたときにフォームをリセット（任意）
    editUserModal.addEventListener('hidden.bs.modal', function (event) {
        const modalForm = editUserModal.querySelector('#editUserForm');
        modalForm.reset(); // フォームをリセット
    });
});
</script>
@endpush