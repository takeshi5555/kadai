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

            {{-- 新規スキル登録 (変更なし) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 mb-0">新規スキル登録</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="/skill" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="new_title" class="form-label">スキル名</label>
                            <input type="text" name="title" id="new_title" class="form-control" placeholder="例: Python入門" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_category" class="form-label">カテゴリ</label>
                            <select name="category" id="new_category" class="form-select" required>
                                <option value="">カテゴリを選択してください</option>
                                @foreach ($categories as $categoryName)
                                    <option value="{{ $categoryName }}" {{ old('category') == $categoryName ? 'selected' : '' }}>
                                        {{ $categoryName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="new_description" class="form-label">説明</label>
                            <textarea name="description" id="new_description" class="form-control" rows="3" placeholder="スキルの詳細な説明" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="skill_image" class="form-label">スキル画像 (任意)</label>
                            <input type="file" name="skill_image" id="skill_image" class="form-control @error('skill_image') is-invalid @enderror" accept="image/*">
                            @error('skill_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="skillImageHelp" class="form-text">JPG, PNG, GIF形式の画像を選択してください (最大2MB)。</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">登録</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 登録済みスキル (この部分をモーダル編集に変更) --}}
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
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>スキル名</th>
                                        <th>カテゴリ</th>
                                        <th>説明</th>
                                        <th>画像</th>
                                        <th>登録日</th>
                                        <th class="text-center">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($skills as $skill)
                                        <tr id="skill-row-{{ $skill->id }}">
                                            <td><strong>{{ $skill->title }}</strong></td>
                                            <td>{{ $skill->category }}</td>
                                            <td>{{ Str::limit($skill->description, 50) }}</td>
                                            <td>
                                                @if ($skill->image_path)
                                                    <img src="{{ Storage::url($skill->image_path) }}" alt="{{ $skill->title }}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                @else
                                                    なし
                                                @endif
                                            </td>
                                            <td>{{ $skill->created_at->format('Y/m/d') }}</td>
                                            <td>
                                    <button type="button" class="btn btn-sm btn-info text-white me-1 edit-skill-btn"
                                            data-bs-toggle="modal" data-bs-target="#editSkillModal"
                                            data-id="{{ $skill->id }}"
                                            data-title="{{ $skill->title }}"
                                            data-description="{{ $skill->description }}"
                                            data-category="{{ $skill->category }}"
                                            data-image="{{ $skill->image_path ? Storage::url($skill->image_path) : '' }}">
                                        <i class="fas fa-edit"></i> 編集
                                    </button>
                                    <form action="{{ route('skill.destroy', $skill->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('本当にこのスキルを削除しますか？');">
                                    @csrf
                                    @method('DELETE') 
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash-alt"></i> 削除
                                    </button>
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

            {{-- ファイルによる一括登録 (変更なし) --}}
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

{{-- CSRFトークンをJavaScriptから参照できるようにする --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="modal fade" id="editSkillModal" tabindex="-1" aria-labelledby="editSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editSkillModalLabel">スキルを編集</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSkillForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editSkillId" name="id">
                    
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">スキル名<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                        <div class="invalid-feedback" id="editTitleError"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">カテゴリ<span class="text-danger">*</span></label>
                        <select class="form-select" id="editCategory" name="category" required>
                            <option value="">カテゴリを選択してください</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="editCategoryError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editDescription" class="form-label">説明<span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editDescription" name="description" rows="5" required></textarea>
                        <div class="invalid-feedback" id="editDescriptionError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editSkillImage" class="form-label">現在のスキル画像</label>
                        <div class="mb-2">
                            {{-- 現在の画像を表示 --}}
                            <img id="currentSkillImage" src="" alt="現在のスキル画像" class="img-thumbnail" style="max-width: 150px; display: none;">
                        </div>
                        
                        <div class="form-check mb-2" id="clearImageCheckContainer" style="display: none;">
                            <input class="form-check-input" type="checkbox" id="clearSkillImage" name="clear_image">
                            <label class="form-check-label" for="clearSkillImage">
                                現在の画像を削除する
                            </label>
                        </div>
                        <label for="editSkillImage" class="form-label">新しいスキル画像 (変更する場合のみ)</label>
                        <input type="file" class="form-control" id="editSkillImage" name="skill_image" accept="image/*">
                        <div id="editSkillImageError" class="invalid-feedback"></div>
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
    const editSkillForm = document.getElementById('editSkillForm');
    const editSkillId = document.getElementById('editSkillId');
    const editTitle = document.getElementById('editTitle');
    const editCategory = document.getElementById('editCategory');
    const editDescription = document.getElementById('editDescription');
    const currentSkillImage = document.getElementById('currentSkillImage');
    const editSkillImageInput = document.getElementById('editSkillImage');
    // ★★★ ここから追加 ★★★
    const clearSkillImageCheckbox = document.getElementById('clearSkillImage');
    const clearImageCheckContainer = document.getElementById('clearImageCheckContainer');
    // ★★★ ここまで追加 ★★★

    // CSRFトークンを取得
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // エラーメッセージ表示用の要素
    const editTitleError = document.getElementById('editTitleError');
    const editCategoryError = document.getElementById('editCategoryError');
    const editDescriptionError = document.getElementById('editDescriptionError');
    const editSkillImageError = document.getElementById('editSkillImageError');

    // モーダルが開かれる直前にデータセットから値を取得してフォームに設定
    editSkillModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        const skillId = button.getAttribute('data-id');
        const skillTitle = button.getAttribute('data-title');
        const skillCategory = button.getAttribute('data-category');
        const skillDescription = button.getAttribute('data-description');
        const skillImagePath = button.getAttribute('data-image'); // data属性から取得

        // フォームフィールドに値をセット
        editSkillId.value = skillId;
        editTitle.value = skillTitle;
        editCategory.value = skillCategory;
        editDescription.value = skillDescription;
        
        // 画像プレビューの設定
        if (skillImagePath && skillImagePath !== '') {
            currentSkillImage.src = skillImagePath;
            currentSkillImage.style.display = 'block';
            // ★★★ ここから追加 ★★★
            clearImageCheckContainer.style.display = 'block'; // 画像がある場合のみチェックボックスを表示
            clearSkillImageCheckbox.checked = false; // モーダルを開くたびにチェックを外す
            // ★★★ ここまで追加 ★★★
        } else {
            currentSkillImage.style.display = 'none';
            // ★★★ ここから追加 ★★★
            clearImageCheckContainer.style.display = 'none'; // 画像がない場合はチェックボックスを非表示
            clearSkillImageCheckbox.checked = false; // 念のためチェックを外す
            // ★★★ ここまで追加 ★★★
        }
        
        editSkillImageInput.value = ''; // ファイル入力フィールドをクリア

        // フォームのアクションURLを更新 (この部分は既存でOK)
        editSkillForm.action = `/skill/${skillId}`;

        // 以前のバリデーションエラーをクリア
        clearValidationErrors();
    });

    // フォーム送信時の処理 (Ajax)
    editSkillForm.addEventListener('submit', function (e) {
        e.preventDefault();

        clearValidationErrors();

        const formData = new FormData(this);
        const skillId = editSkillId.value;

        // PUTメソッドであることをLaravelに伝える
        formData.append('_method', 'PUT'); 
        
        // CSRFトークンをフォームデータに追加
        // formData.append('_token', csrfToken); // Headersに含めるのでFormDataには不要（重複を防ぐ）

        fetch(`/skill/${skillId}`, {
            method: 'POST', // FormDataと_method='PUT'を使うためPOSTのままでOK
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest', // AjaxリクエストであることをLaravelに伝える
                'X-CSRF-TOKEN': csrfToken // CSRFトークンをヘッダーにも追加
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('サーバーからの応答が無効です。ページをリロードしてください。');
            }

            if (response.ok) {
                return response.json();
            } else if (response.status === 422) {
                return response.json().then(data => {
                    throw { status: 422, errors: data.errors };
                });
            } else {
                return response.json().then(data => {
                    throw new Error(data.message || '更新に失敗しました。');
                });
            }
        })
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(editSkillModal);
                modal.hide();

                // テーブルの該当行をJavaScriptで更新
                updateTableRow(skillId, data.skill);

                // 成功メッセージの表示
                showSuccessMessage(data.message);

            } else {
                alert(data.message || '不明なエラーが発生しました。');
            }
        })
        .catch(error => {
            if (error.status === 422 && error.errors) {
                displayValidationErrors(error.errors);
            } else {
                alert('エラー: ' + error.message);
                console.error('Error:', error);
            }
        });
    });

    // テーブル行を更新する関数 (前回の修正を適用済みとしてそのまま利用)
    function updateTableRow(skillId, skill) {
        const row = document.getElementById(`skill-row-${skillId}`);
        if (row) {
            row.querySelector('td:nth-child(1)').innerHTML = `<strong>${skill.title}</strong>`;
            row.querySelector('td:nth-child(2)').textContent = skill.category;
            row.querySelector('td:nth-child(3)').textContent = skill.description.substring(0, 50) + (skill.description.length > 50 ? '...' : '');
            
            const imageCell = row.querySelector('td:nth-child(4)');
            if (skill.image_path) {
                let imageSrc;
                if (skill.image_path.startsWith('http')) {
                    imageSrc = skill.image_path;
                } else if (skill.image_path.startsWith('/storage/')) {
                    imageSrc = skill.image_path;
                } else {
                    imageSrc = '/storage/' + skill.image_path;
                }
                // キャッシュ対策としてタイムスタンプを追加
                imageSrc += '?t=' + new Date().getTime(); 

                imageCell.innerHTML = `<img src="${imageSrc}" alt="${skill.title}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">`;
            } else {
                imageCell.textContent = 'なし'; // 画像がない場合は「なし」と表示
                imageCell.innerHTML = 'なし'; // imgタグも削除されるようにinnerHTMLを使う
            }

            // 編集ボタンのdata属性も更新
            const editButton = row.querySelector('.edit-skill-btn');
            if (editButton) {
                let dataImageSrc;
                if (skill.image_path) { // 画像パスが存在する場合のみdataImageSrcを設定
                    if (skill.image_path.startsWith('http')) {
                        dataImageSrc = skill.image_path;
                    } else if (skill.image_path.startsWith('/storage/')) {
                        dataImageSrc = skill.image_path;
                    } else {
                        dataImageSrc = '/storage/' + skill.image_path;
                    }
                } else {
                    dataImageSrc = ''; // 画像がない場合は空文字列
                }
                
                editButton.setAttribute('data-title', skill.title);
                editButton.setAttribute('data-category', skill.category);
                editButton.setAttribute('data-description', skill.description);
                editButton.setAttribute('data-image', dataImageSrc);
            }
        }
    }

    // 成功メッセージを表示する関数
    function showSuccessMessage(message) {
        // ... (省略) ... 以前のコードのまま
        const existingSuccessAlert = document.querySelector('.alert-success');
        if (existingSuccessAlert) {
            existingSuccessAlert.remove();
        }
        
        const newAlert = document.createElement('div');
        newAlert.className = 'alert alert-success alert-dismissible fade show';
        newAlert.role = 'alert';
        newAlert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // メッセージを表示する場所のセレクタを確認し、適宜修正してください。
        // `document.querySelector('.container .row .col-md-10')` は特定のレイアウトに依存します
        // 今回はシンプルに、ページトップの body の直後に追加する例を示します
        const mainContent = document.querySelector('main.py-4.pt-5.mt-5');
        if (mainContent) {
            mainContent.insertAdjacentElement('afterbegin', newAlert);
        } else {
            document.body.prepend(newAlert);
        }

        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(newAlert);
            alert.close();
        }, 3000);
    }

    // バリデーションエラーを表示する関数 (省略)
    function displayValidationErrors(errors) {
        document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        if (errors.title) {
            editTitle.classList.add('is-invalid');
            editTitleError.textContent = errors.title[0];
        }
        if (errors.category) {
            editCategory.classList.add('is-invalid');
            editCategoryError.textContent = errors.category[0];
        }
        if (errors.description) {
            editDescription.classList.add('is-invalid');
            editDescriptionError.textContent = errors.description[0];
        }
        if (errors.skill_image) {
            editSkillImageInput.classList.add('is-invalid');
            editSkillImageError.textContent = errors.skill_image[0];
        }
    }

    // バリデーションエラーをクリアする関数 (省略)
    function clearValidationErrors() {
        document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }
});
</script>
@endpush