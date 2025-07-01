@extends('layouts.admin')

@section('title', 'スキル管理')

@push('styles')
<style>
    :root {
    --skillswap-primary:rgb(110, 161, 209); /* 新しいメインブルー（少し落ち着いた青） */
    --skillswap-primary-dark:rgb(121, 165, 203); /* メインカラーより濃い青（ホバー用やアクセントに） */
    --skillswap-text-light: #ffffff; /* 明るい背景用テキスト（白） */
    --skillswap-text-dark: #333333; /* 暗い背景用テキスト（濃いグレー） */
    --status-success-light: #C5E1F7; /* 薄い青 */
    --status-info: #6c757d; /* ミディアムグレー */
    --status-info-dark: #5a6268;
}
    /* テーブルヘッダーのテキスト色を調整 */
    .table thead th {
        color: var(--skillswap-text-dark); /* ダークカラーのテキストに */
    }
    /* ソートアイコンの色を調整 */
    .table thead th a {
        color: var(--skillswap-primary) !important; /* メインカラーに */
    }
    .table thead th a:hover {
        color: var(--skillswap-primary-dark) !important; /* ホバーで少し濃く */
    }
    /* 編集ボタンのスタイルを調整 (btn-infoを上書き) */
  .btn-info {
        background-color: var(--status-info) !important; /* グレー系の色に修正 */
        border-color: var(--status-info) !important;
        color: var(--skillswap-text-light) !important; /* 白文字 */
    }
    .btn-info:hover {
        background-color: var(--status-info-dark) !important; /* ホバーで少し濃いグレーに */
        border-color: var(--status-info-dark) !important;
    }
    /* モーダルのヘッダーにメインカラーを適用 */
    #editSkillModal .modal-header {
        background-color: var(--skillswap-primary-dark);
        color: var(--skillswap-text-light);
    }
    /* モーダルのタイトル色を調整 */
    #editSkillModal .modal-title {
        color: var(--skillswap-text-light);
    }

    /* --- ★★★ ここから修正：!importantを強化 ★★★ --- */
    /* スキル一覧のカードヘッダーの背景色を btn-skill-info と同じ薄い青に */
    .card-header.skill-list-header {
        background-color: var(--status-success-light) !important; /* 薄い青を背景に */
        border-bottom: 1px solid var(--skillswap-primary) !important; /* ボーダーも合わせて調整 */
        color: var(--skillswap-text-dark) !important; /* ヘッダーの文字色も考慮 */
    }

    /* 「検索」ボタンの色を btn-skill-info と同じ薄い青に */
    .btn-skill-search {
        background-color: var(--status-success-light) !important; /* 薄い青 */
        border-color: var(--skillswap-primary) !important; /* メインの青で枠線 */
        color: var(--skillswap-text-dark) !important; /* 文字色を濃い色に（背景が薄いため）*/
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    .btn-skill-search:hover {
        background-color: var(--skillswap-primary) !important; /* ホバー時はメインの青 */
        border-color: var(--skillswap-primary-dark) !important;
        color: var(--skillswap-text-light) !important; /* ホバー時のテキストは白に */
    }
    /* --- ★★★ ここまで修正 ★★★ --- */
</style>
@endpush

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
        {{-- ★★★ ここは変更なし ★★★ --}}
        <div class="card-header skill-list-header">
            スキル一覧
        </div>
        <div class="card-body">
            {{-- 検索フォーム --}}
            <form action="{{ route('admin.skills.index') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="タイトルまたは説明で検索" value="{{ request('search') }}">
                    {{-- ★★★ ここは変更なし ★★★ --}}
                    <button class="btn btn-skill-search ms-2" type="submit">検索</button>
                    @if(request('search'))
                        <a href="{{ route('admin.skills.index') }}" class="btn btn-danger">クリア</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>
                                <div class="d-flex align-items-center">
                                    ID
                                    <a href="{{ route('admin.skills.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => (request('sort') == 'id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
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
                            <th>タイトル</th>
                            <th>説明</th>
                            <th>
                                <div class="d-flex align-items-center">
                                    カテゴリ
                                    <a href="{{ route('admin.skills.index', array_merge(request()->query(), ['sort' => 'category', 'direction' => (request('sort') == 'category' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'category')
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
                            <th>
                                <div class="d-flex align-items-center">
                                    所有者
                                    <a href="{{ route('admin.skills.index', array_merge(request()->query(), ['sort' => 'user_id', 'direction' => (request('sort') == 'user_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'user_id')
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
                            <th>
                                <div class="d-flex align-items-center">
                                    登録日
                                    <a href="{{ route('admin.skills.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => (request('sort') == 'created_at' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'created_at')
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
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($skills as $skill)
                            <tr>
                                <td>{{ $skill->id }}</td>
                                <td>{{ $skill->title }}</td>
                                <td>{{ Str::limit($skill->description, 50) }}</td>
                                <td>{{ $skill->category }}</td> 
                                <td>{{ $skill->user->name ?? '不明' }}</td> 
                                <td>{{ $skill->created_at ? $skill->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info text-white me-1 edit-skill-btn"
                                            data-bs-toggle="modal" data-bs-target="#editSkillModal"
                                            data-id="{{ $skill->id }}"
                                            data-title="{{ $skill->title }}"
                                            data-description="{{ $skill->description }}"
                                            data-category="{{ $skill->category }}"> 
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
                        <label for="modalSkillCategory" class="form-label">カテゴリ</label>
                        <select class="form-select" id="modalSkillCategory" name="category" required 
                                onchange="handleModalCategoryChange(this)">
                            <option value="">カテゴリを選択してください</option>
                            @foreach ($categories as $categoryName)
                                <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                            @endforeach
                            <option value="custom_category">新しいカテゴリを追加...</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="modalCustomCategoryInput" name="new_custom_category" placeholder="新しいカテゴリ名を入力" style="display:none;">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editSkillModal = document.getElementById('editSkillModal');

    editSkillModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; 

        const skillId = button.getAttribute('data-id');
        const skillTitle = button.getAttribute('data-title');
        const skillDescription = button.getAttribute('data-description');
        const skillCategory = button.getAttribute('data-category');

        const modalForm = editSkillModal.querySelector('#editSkillForm');
        const modalTitleInput = editSkillModal.querySelector('#modalSkillTitle');
        const modalDescriptionTextarea = editSkillModal.querySelector('#modalSkillDescription');
        const modalCategorySelect = editSkillModal.querySelector('#modalSkillCategory');
        const modalCustomCategoryInput = editSkillModal.querySelector('#modalCustomCategoryInput');

        modalForm.action = `/admin/skills/${skillId}`; 

        modalTitleInput.value = skillTitle;
        modalDescriptionTextarea.value = skillDescription;

        const categories = @json($categories ?? []); 

        if (categories.includes(skillCategory)) {
            modalCategorySelect.value = skillCategory;
            modalCustomCategoryInput.style.display = 'none';
            modalCustomCategoryInput.removeAttribute('required');
            modalCustomCategoryInput.name = 'new_custom_category'; 
            modalCategorySelect.name = 'category'; 
        } else {
            modalCategorySelect.value = 'custom_category'; 
            modalCustomCategoryInput.style.display = 'block'; 
            modalCustomCategoryInput.setAttribute('required', 'required'); 
            modalCustomCategoryInput.value = skillCategory; 
            modalCustomCategoryInput.name = 'category'; 
            modalCategorySelect.removeAttribute('name'); 
        }
    });

    editSkillModal.addEventListener('hidden.bs.modal', function (event) {
        const modalForm = editSkillModal.querySelector('#editSkillForm');
        modalForm.reset(); 

        const modalCustomCategoryInput = editSkillModal.querySelector('#modalCustomCategoryInput');
        if (modalCustomCategoryInput) {
            modalCustomCategoryInput.style.display = 'none';
            modalCustomCategoryInput.removeAttribute('required');
            modalCustomCategoryInput.name = 'new_custom_category'; 
            editSkillModal.querySelector('#modalSkillCategory').name = 'category';
        }
    });

    function handleModalCategoryChange(selectElement) {
        const customCategoryInput = document.getElementById('modalCustomCategoryInput');
        if (selectElement.value === 'custom_category') {
            customCategoryInput.style.display = 'block';
            customCategoryInput.setAttribute('required', 'required');
            customCategoryInput.name = 'category'; 
            selectElement.removeAttribute('name'); 
        } else {
            customCategoryInput.style.display = 'none';
            customCategoryInput.removeAttribute('required');
            customCategoryInput.name = 'new_custom_category'; 
            selectElement.name = 'category'; 
        }
    }
    window.handleModalCategoryChange = handleModalCategoryChange; 
});
</script>
@endpush
@endsection