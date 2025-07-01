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
        {{-- カードヘッダーをテーマカラーに --}}
        <div class="card-header user-card-header text-white">
            ユーザー一覧
        </div>
        <div class="card-body">
            {{-- 検索フォーム --}}
            <form action="{{ route('admin.users.index') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="名前またはメールアドレスで検索" value="{{ request('search') }}">
                    {{-- 検索ボタンをメインカラーに --}}
                    <button class="btn btn-primary" type="submit">検索</button>
                    @if(request('search'))
                        {{-- クリアボタンは危険色に --}}
                        <a href="{{ route('admin.users.index') }}" class="btn btn-danger ms-2">クリア</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped user-table"> {{-- カスタムクラスを追加 --}}
                    <thead>
                        <tr>
                            <th>
                                <div class="d-flex align-items-center">
                                    ID
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => (request('sort') == 'id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none user-sort-link">
                                        @if(request('sort') == 'id')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th>名前</th>
                            <th>メールアドレス</th>
                            <th>
                                <div class="d-flex align-items-center">
                                    ロール
                                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'role', 'direction' => (request('sort') == 'role' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none user-sort-link">
                                        @if(request('sort') == 'role')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
                                        @endif
                                    </a>
                                </div>
                            </th>
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
                                <td><span class="badge {{ $user->role === 'admin' ? 'bg-danger' : ($user->role === 'moderator' ? 'user-role-moderator-badge' : 'bg-secondary') }}">{{ ucfirst($user->role) }}</span></td> {{-- moderatorはカスタムバッジに --}}
                                <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    {{-- 編集ボタンをモーダル起動用に変更 --}}
                                    <button type="button" class="btn btn-sm btn-secondary me-1 edit-user-btn" {{-- グレー系ボタンに --}}
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
                {{-- ページネーションリンクにもカスタムスタイルを適用 --}}
                 {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ユーザー編集モーダル --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- モーダルヘッダーもテーマカラーに --}}
            <div class="modal-header user-modal-header text-white">
                <h5 class="modal-title" id="editUserModalLabel">ユーザー編集</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> {{-- 閉じるボタンの色を白に --}}
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

---

@push('styles')
<style>
/* --- CSS変数の定義（必須） --- */
:root {
    --skillswap-primary:rgb(110, 161, 209); /* メインブルー（落ち着いた青） */
    --skillswap-primary-dark:rgb(121, 165, 203); /* メインカラーより濃い青（ホバー用やアクセントに） */
    --skillswap-text-light: #ffffff; /* 明るい背景用テキスト（白） */
    --skillswap-text-dark: #333333; /* 暗い背景用テキスト（濃いグレー） */

    /* このページで使うステータスカラー */
    --status-info: #6c757d; /* ミディアムグレー (btn-secondary, user role: user) */
    --status-info-dark: #5a6268;
    --status-warning: #ffc107; /* Bootstrapのwarning (user role: moderator) */
    --status-warning-dark: #e0a800;
    --status-danger: #dc3545; /* Bootstrapのdanger (user role: admin) */
    --status-danger-dark: #c82333;
}

/* --- カードヘッダーとモーダルヘッダーの色 --- */
.user-card-header,
.user-modal-header {
    background-color: var(--skillswap-primary-dark) !important; /* 濃い青を適用 */
    color: var(--skillswap-text-light) !important;
    font-weight: bold;
    border-bottom: 1px solid var(--skillswap-primary-dark) !important;
}

/* モーダルの閉じるボタンを白に */
.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%); /* 白に変換 */
}


/* --- ボタンのスタイル調整 --- */

/* メインのアクションボタン (btn-primary) */
.btn-primary {
    background-color: var(--skillswap-primary) !important;
    border-color: var(--skillswap-primary) !important;
    color: var(--skillswap-text-light) !important;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-primary:hover {
    background-color: var(--skillswap-primary-dark) !important;
    border-color: var(--skillswap-primary-dark) !important;
    color: var(--skillswap-text-light) !important;
}

/* btn-secondary の調整（落ち着いたグレー） */
.btn-secondary {
    background-color: var(--status-info) !important;
    border-color: var(--status-info) !important;
    color: var(--skillswap-text-light) !important; /* 文字色を白に */
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-secondary:hover {
    background-color: var(--status-info-dark) !important;
    border-color: var(--status-info-dark) !important;
    color: var(--skillswap-text-light) !important;
}

/* btn-danger の調整（削除ボタンなど） */
.btn-danger {
    background-color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
    color: var(--skillswap-text-light) !important;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-danger:hover {
    background-color: var(--status-danger-dark) !important;
    border-color: var(--status-danger-dark) !important;
    color: var(--skillswap-text-light) !important;
}

/* --- テーブルとソートリンクのスタイル --- */
.user-table th {
    background-color: var(--skillswap-primary); /* ヘッダーの背景色をメインカラーに */
    color: var(--skillswap-text-light); /* 文字色を白に */
    border-color: var(--skillswap-primary-dark); /* ボーダー色を濃い青に */
    vertical-align: middle; /* 垂直方向中央揃え */
}

.user-sort-link {
    color: var(--skillswap-text-light) !important; /* ソートリンクの文字色を白に */
    opacity: 0.8; /* 少し透明度をつけて馴染ませる */
}
.user-sort-link:hover {
    opacity: 1; /* ホバーで不透明に */
}

/* --- ロールバッジのスタイル --- */
.user-role-moderator-badge { /* moderator (warning) バッジのカスタム */
    background-color: var(--status-warning) !important;
    color: var(--skillswap-text-dark) !important; /* 背景色に合わせて文字色を濃いグレーに */
}

/* --- ページネーションのスタイル --- */
.pagination .page-item .page-link {
    color: var(--skillswap-primary-dark); /* 通常のページリンクは濃い青 */
}
.pagination .page-item.active .page-link {
    background-color: var(--skillswap-primary) !important; /* アクティブなページはメインの青 */
    border-color: var(--skillswap-primary) !important;
    color: var(--skillswap-text-light) !important; /* 文字色を白に */
}
.pagination .page-item:not(.active) .page-link:hover {
    background-color: var(--status-info-light) !important; /* ホバー時は薄いグレー */
    color: var(--skillswap-primary-dark) !important;
}
.pagination .page-item.disabled .page-link {
    color: var(--status-info) !important; /* 無効なリンクはグレー */
}

/* 不要なスタイルを削除済み */
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-id');
        const userName = button.getAttribute('data-name');
        const userEmail = button.getAttribute('data-email');
        const userRole = button.getAttribute('data-role');

        const modalForm = editUserModal.querySelector('#editUserForm');
        const modalNameInput = editUserModal.querySelector('#modalUserName');
        const modalEmailInput = editUserModal.querySelector('#modalUserEmail');
        const modalRoleSelect = editUserModal.querySelector('#modalUserRole');

        modalForm.action = `/admin/users/${userId}`;
        modalNameInput.value = userName;
        modalEmailInput.value = userEmail;
        modalRoleSelect.value = userRole;
    });

    editUserModal.addEventListener('hidden.bs.modal', function (event) {
        const modalForm = editUserModal.querySelector('#editUserForm');
        modalForm.reset();
    });
});
</script>
@endpush