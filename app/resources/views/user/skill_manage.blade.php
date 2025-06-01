@extends('layouts.app')

@section('title', 'スキル管理')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="mb-4 text-center">スキル管理</h1>

                {{-- セッションメッセージ --}}
                @if (session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- バリデーションエラーメッセージ --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- 新規スキル登録 --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h5 mb-0">新規スキル登録</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/skill">
                            @csrf
                            <div class="mb-3">
                                <label for="new_title" class="form-label">スキル名</label>
                                <input type="text" name="title" id="new_title" class="form-control" placeholder="例: Python入門" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_category" class="form-label">カテゴリ</label>
                                <input type="text" name="category" id="new_category" class="form-control" placeholder="例: プログラミング" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_description" class="form-label">説明</label>
                                <textarea name="description" id="new_description" class="form-control" rows="3" placeholder="スキルの詳細な説明" required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">登録</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 登録済みスキル --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h2 class="h5 mb-0">登録済みスキル</h2>
                    </div>
                    <div class="card-body">
                        @if ($skills->isEmpty())
                            <div class="alert alert-info" role="alert">
                                まだ登録済みのスキルはありません。
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle"> {{-- align-middleで垂直方向中央寄せ --}}
                                    <thead>
                                        <tr>
                                            <th>スキル名</th>
                                            <th>カテゴリ</th>
                                            <th>説明</th>
                                            <th class="text-center pe-5">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($skills as $skill)
                                            <tr id="skill-row-{{ $skill->id }}">
                                                {{-- 表示モード --}}
                                                <td id="view-title-{{ $skill->id }}" class="col-3"><strong>{{ $skill->title }}</strong></td>
                                                <td id="view-category-{{ $skill->id }}" class="col-2">{{ $skill->category }}</td>
                                                <td id="view-description-{{ $skill->id }}" class="col-4">{!! nl2br(e($skill->description)) !!}</td>
                                                <td id="view-actions-{{ $skill->id }}" class="col-3 text-end text-nowrap"> {{-- text-nowrapでボタンが改行されないように --}}
                                                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="toggleEdit({{ $skill->id }})">編集</button>
                                                    <form method="POST" action="/skill/{{ $skill->id }}/delete" onsubmit="return confirm('本当にこのスキルを削除しますか？');" class="d-inline-block">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">削除</button>
                                                    </form>
                                                </td>

                                                {{-- 編集モード --}}
                                                <td id="edit-{{ $skill->id }}" colspan="4" style="display:none;">
                                                    <form method="POST" action="/skill/{{ $skill->id }}/update" class="row g-2 align-items-center">
                                                        @csrf
                                                        <div class="col-md-3">
                                                            <input type="text" name="title" value="{{ $skill->title }}" class="form-control form-control-sm" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" name="category" value="{{ $skill->category }}" class="form-control form-control-sm" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <textarea name="description" class="form-control form-control-sm" rows="1" required>{{ $skill->description }}</textarea>
                                                        </div>
                                                        <div class="col-md-3 text-end text-nowrap">
                                                            <button type="submit" class="btn btn-sm btn-primary me-2">更新</button>
                                                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEdit({{ $skill->id }})">キャンセル</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ファイルによる一括登録 --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h2 class="h5 mb-0">スキルの一括登録 (CSV/Excel)</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/skill/import" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="skill_file" class="form-label">ファイルを選択</label>
                                <input type="file" name="skill_file" id="skill_file" accept=".csv,.xlsx" class="form-control" required>
                                <div id="fileHelp" class="form-text">CSVまたはExcelファイル (.csv, .xlsx) を選択してください。</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-info">ファイル読み込み</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- JavaScript for toggleEdit --}}
    <script>
        function toggleEdit(id) {
            const viewTitle = document.getElementById('view-title-' + id);
            const viewCategory = document.getElementById('view-category-' + id);
            const viewDescription = document.getElementById('view-description-' + id);
            const viewActions = document.getElementById('view-actions-' + id);

            const editFormCell = document.getElementById('edit-' + id);

            if (editFormCell.style.display === 'none') {
              
                viewTitle.style.display = 'none';
                viewCategory.style.display = 'none';
                viewDescription.style.display = 'none';
                viewActions.style.display = 'none';
                editFormCell.style.display = 'table-cell'; 
            } else {
                viewTitle.style.display = 'table-cell'; 
                viewCategory.style.display = 'table-cell';
                viewDescription.style.display = 'table-cell';
                viewActions.style.display = 'table-cell';
                editFormCell.style.display = 'none';
            }
        }
    </script>
@endsection

<style>
    .card {
        --bs-card-height: auto;
        height: auto !important;
    }

    .skill-detail-card {
        min-height: 200px; 

    }

    .skill-provider-card {
        min-height: 200px;
    }

</style>