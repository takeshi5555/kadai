@extends('layouts.admin')

@section('title', 'スキル管理')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">スキル管理</h1>

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
        <div class="card-header bg-success text-white">
            スキル一覧
        </div>
        <div class="card-body">
            {{-- 検索フォーム --}}
            <form action="{{ route('admin.skills.index') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="タイトルまたは説明で検索" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary ms-2" type="submit">検索</button>
                    @if(request('search'))
                        <a href="{{ route('admin.skills.index') }}" class="btn btn-outline-danger">クリア</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>タイトル</th>
                            <th>説明</th>
                            <th>所有者</th>
                            <th>登録日</th>
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($skills as $skill)
                            <tr>
                                <td>{{ $skill->id }}</td>
                                <td>{{ $skill->title }}</td>
                                <td>{{ Str::limit($skill->description, 50) }}</td> {{-- 説明を短く表示 --}}
                                <td>{{ $skill->user->name ?? '不明' }}</td> {{-- リレーションがあれば表示 --}}
                                <td>{{ $skill->created_at ? $skill->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info text-white me-1 edit-skill-btn"
                                            data-bs-toggle="modal" data-bs-target="#editSkillModal"
                                            data-id="{{ $skill->id }}"
                                            data-title="{{ $skill->title }}"
                                            data-description="{{ $skill->description }}">
                                        編集
                                    </button>
                                    <form action="{{ route('admin.skills.destroy', $skill) }}" method="POST" class="d-inline-block" onsubmit="return confirm('本当にこのスキルを削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">スキルが見つかりません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーションリンク --}}
            <div class="d-flex justify-content-center">
                {{ $skills->appends(request()->query())->links('pagination::simple-bootstrap-4') }}

            </div>
        </div>
    </div>
</div>



{{-- スキル編集モーダル --}}
<div class="modal fade" id="editSkillModal" tabindex="-1" aria-labelledby="editSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSkillModalLabel">スキル編集</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSkillForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalSkillTitle" class="form-label">タイトル</label>
                        <input type="text" class="form-control" id="modalSkillTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalSkillDescription" class="form-label">説明</label>
                        <textarea class="form-control" id="modalSkillDescription" name="description" rows="5"></textarea>
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


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editSkillModal = document.getElementById('editSkillModal');
    editSkillModal.addEventListener('show.bs.modal', function (event) {
        // モーダルを開くボタン
        const button = event.relatedTarget;

        // データ属性からスキル情報を取得
        const skillId = button.getAttribute('data-id');
        const skillTitle = button.getAttribute('data-title');
        const skillDescription = button.getAttribute('data-description');
        const skillIsActive = button.getAttribute('data-is_active');

        // モーダル内のフォーム要素を取得
        const modalForm = editSkillModal.querySelector('#editSkillForm');
        const modalTitleInput = editSkillModal.querySelector('#modalSkillTitle');
        const modalDescriptionTextarea = editSkillModal.querySelector('#modalSkillDescription');
        const modalIsActiveCheckbox = editSkillModal.querySelector('#modalSkillIsActive');

        // フォームのアクションURLを設定
        // 例: /admin/skills/{id}
        modalForm.action = `/admin/skills/${skillId}`;

        // フォームにスキル情報を設定
        modalTitleInput.value = skillTitle;
        modalDescriptionTextarea.value = skillDescription;
        modalIsActiveCheckbox.checked = (skillIsActive === '1'); // '1'の場合はチェック
    });

    // モーダルが閉じられたときにフォームをリセット（任意）
    editSkillModal.addEventListener('hidden.bs.modal', function (event) {
        const modalForm = editSkillModal.querySelector('#editSkillForm');
        modalForm.reset(); // フォームをリセット
    });
});
</script>
@endpush