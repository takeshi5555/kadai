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

            {{-- 新規スキル登録 (非同期対応) --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 mb-0">新規スキル登録</h2>
                </div>
                <div class="card-body">
                    <form id="newSkillForm" method="POST" action="/skill" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="new_title" class="form-label">スキル名<span class="text-danger">*</span></label>
                            <input type="text" name="title" id="new_title" class="form-control" placeholder="例: Python入門" required>
                            <div class="invalid-feedback" id="newTitleError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="new_category" class="form-label">カテゴリ<span class="text-danger">*</span></label>
                            <select name="category" id="new_category" class="form-select" required>
                                <option value="">カテゴリを選択してください</option>
                                @foreach ($categories as $categoryName)
                                    <option value="{{ $categoryName }}" {{ old('category') == $categoryName ? 'selected' : '' }}>
                                        {{ $categoryName }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="newCategoryError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="new_description" class="form-label">説明<span class="text-danger">*</span></label>
                            <textarea name="description" id="new_description" class="form-control" rows="3" placeholder="スキルの詳細な説明" required></textarea>
                            <div class="invalid-feedback" id="newDescriptionError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="skill_image" class="form-label">スキル画像 (任意)</label>
                            <input type="file" name="skill_image" id="skill_image" class="form-control @error('skill_image') is-invalid @enderror" accept="image/*">
                            <div class="invalid-feedback" id="newSkillImageError">
                                @error('skill_image'){{ $message }}@enderror
                            </div>
                            <div id="skillImageHelp" class="form-text">JPG, PNG, GIF形式の画像を選択してください (最大2MB)。</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="newSkillSubmitBtn">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" id="newSkillSpinner" style="display: none;"></span>
                                登録
                            </button>
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
                    <div id="skillsTableContainer">
                        @if ($skills->isEmpty())
                            <div class="alert alert-info" role="alert" id="noSkillsMessage">
                                まだ登録済みのスキルはありません。
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle" id="skillsTable">
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
                                    <tbody id="skillsTableBody">
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
                                            {{-- ★ここを修正: type="button" と delete-skill-btn クラスを追加★ --}}
                                            <button type="button" class="btn btn-sm btn-danger delete-skill-btn"
                                                    data-skill-id="{{ $skill->id }}" {{-- JavaScriptで使用するdata属性をここに移動 --}}
                                                    data-skill-title="{{ $skill->title }}"> {{-- JavaScriptで使用するdata属性をここに移動 --}}
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
    // CSRFトークンを取得
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ========== 新規スキル登録の非同期化 ==========
    const newSkillForm = document.getElementById('newSkillForm');
    const newTitle = document.getElementById('new_title');
    const newCategory = document.getElementById('new_category');
    const newDescription = document.getElementById('new_description');
    const newSkillImage = document.getElementById('skill_image');
    const newSkillSubmitBtn = document.getElementById('newSkillSubmitBtn');
    const newSkillSpinner = document.getElementById('newSkillSpinner');

    // エラーメッセージ表示用の要素
    const newTitleError = document.getElementById('newTitleError');
    const newCategoryError = document.getElementById('newCategoryError');
    const newDescriptionError = document.getElementById('newDescriptionError');
    const newSkillImageError = document.getElementById('newSkillImageError');

    // 新規スキル登録フォーム送信時の処理
    newSkillForm.addEventListener('submit', function (e) {
        e.preventDefault();

        clearNewSkillValidationErrors();
        
        // ローディング状態にする
        newSkillSubmitBtn.disabled = true;
        newSkillSpinner.style.display = 'inline-block';

        const formData = new FormData(this);

        fetch('/skill', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
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
                    throw new Error(data.message || '登録に失敗しました。');
                });
            }
        })
        .then(data => {
            if (data.success) {
                // フォームをリセット
                newSkillForm.reset();
                
                // テーブルに新しい行を追加
                addNewSkillToTable(data.skill);
                
                // 成功メッセージを表示
                showSuccessMessage(data.message);
                
            } else {
                alert(data.message || '不明なエラーが発生しました。');
            }
        })
        .catch(error => {
            if (error.status === 422 && error.errors) {
                displayNewSkillValidationErrors(error.errors);
            } else {
                alert('エラー: ' + error.message);
                console.error('Error:', error);
            }
        })
        .finally(() => {
            // ローディング状態を解除
            newSkillSubmitBtn.disabled = false;
            newSkillSpinner.style.display = 'none';
        });
    });

    // 新しいスキルをテーブルに追加する関数
    function addNewSkillToTable(skill) {
        const skillsTableContainer = document.getElementById('skillsTableContainer');
        const noSkillsMessage = document.getElementById('noSkillsMessage');
        const skillsTable = document.getElementById('skillsTable');
        const skillsTableBody = document.getElementById('skillsTableBody');

        // 「まだ登録済みのスキルがありません」メッセージを非表示にし、テーブルを表示
        if (noSkillsMessage) {
            noSkillsMessage.style.display = 'none';
        }

        // テーブルが存在しない場合は作成
        if (!skillsTable) {
            const tableHtml = `
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="skillsTable">
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
                        <tbody id="skillsTableBody">
                        </tbody>
                    </table>
                </div>
            `;
            skillsTableContainer.innerHTML = tableHtml;
        }

        // 新しい行を作成
        const newRow = createSkillTableRow(skill);
        
        // テーブルの先頭に追加（最新のスキルを上に表示）
        const tbody = document.getElementById('skillsTableBody');
        tbody.insertAdjacentHTML('afterbegin', newRow);
    }

    // スキルテーブル行を作成する関数（修正版）
function createSkillTableRow(skill) {
    const imageCell = skill.image_path 
        ? `<img src="${getImageSrc(skill.image_path)}" alt="${skill.title}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">`
        : 'なし';

    const formattedDate = new Date(skill.created_at).toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).replace(/\//g, '/');

    return `
        <tr id="skill-row-${skill.id}">
            <td><strong>${skill.title}</strong></td>
            <td>${skill.category}</td>
            <td>${skill.description.substring(0, 50)}${skill.description.length > 50 ? '...' : ''}</td>
            <td>${imageCell}</td>
            <td>${formattedDate}</td>
            <td>
                <button type="button" class="btn btn-sm btn-info text-white me-1 edit-skill-btn"
                        data-bs-toggle="modal" data-bs-target="#editSkillModal"
                        data-id="${skill.id}"
                        data-title="${skill.title}"
                        data-description="${skill.description}"
                        data-category="${skill.category}"
                        data-image="${skill.image_path ? getImageSrc(skill.image_path) : ''}">
                    <i class="fas fa-edit"></i> 編集
                </button>
                <form action="/skill/${skill.id}" method="POST" class="d-inline-block" onsubmit="return confirm('本当にこのスキルを削除しますか？');">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" class="btn btn-sm btn-danger delete-skill-btn"
                            data-skill-id="${skill.id}"
                            data-skill-title="${skill.title}">
                        <i class="fas fa-trash-alt"></i> 削除
                    </button>
                </form>
            </td>
        </tr>
    `;
}

    // 画像パスを正しいURLに変換する関数
    function getImageSrc(imagePath) {
        if (imagePath.startsWith('http')) {
            return imagePath;
        } else if (imagePath.startsWith('/storage/')) {
            return imagePath;
        } else {
            return '/storage/' + imagePath;
        }
    }

    // 新規スキル登録のバリデーションエラーを表示する関数
    function displayNewSkillValidationErrors(errors) {
        if (errors.title) {
            newTitle.classList.add('is-invalid');
            newTitleError.textContent = errors.title[0];
        }
        if (errors.category) {
            newCategory.classList.add('is-invalid');
            newCategoryError.textContent = errors.category[0];
        }
        if (errors.description) {
            newDescription.classList.add('is-invalid');
            newDescriptionError.textContent = errors.description[0];
        }
        if (errors.skill_image) {
            newSkillImage.classList.add('is-invalid');
            newSkillImageError.textContent = errors.skill_image[0];
        }
    }

    // 新規スキル登録のバリデーションエラーをクリアする関数
    function clearNewSkillValidationErrors() {
        [newTitle, newCategory, newDescription, newSkillImage].forEach(el => el.classList.remove('is-invalid'));
        [newTitleError, newCategoryError, newDescriptionError, newSkillImageError].forEach(el => el.textContent = '');
    }

    // ========== 既存のモーダル編集機能 ==========
    const editSkillModal = document.getElementById('editSkillModal');
    const editSkillForm = document.getElementById('editSkillForm');
    const editSkillId = document.getElementById('editSkillId');
    const editTitle = document.getElementById('editTitle');
    const editCategory = document.getElementById('editCategory');
    const editDescription = document.getElementById('editDescription');
    const currentSkillImage = document.getElementById('currentSkillImage');
    const editSkillImageInput = document.getElementById('editSkillImage');
    const clearSkillImageCheckbox = document.getElementById('clearSkillImage');
    const clearImageCheckContainer = document.getElementById('clearImageCheckContainer');

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
        const skillImagePath = button.getAttribute('data-image');

        // フォームフィールドに値をセット
        editSkillId.value = skillId;
        editTitle.value = skillTitle;
        editCategory.value = skillCategory;
        editDescription.value = skillDescription;
        
        // 画像プレビューの設定
        if (skillImagePath && skillImagePath !== '') {
            currentSkillImage.src = skillImagePath;
            currentSkillImage.style.display = 'block';
            clearImageCheckContainer.style.display = 'block';
            clearSkillImageCheckbox.checked = false;
        } else {
            currentSkillImage.style.display = 'none';
            clearImageCheckContainer.style.display = 'none';
            clearSkillImageCheckbox.checked = false;
        }
        
        editSkillImageInput.value = '';

        // フォームのアクションURLを更新
        editSkillForm.action = `/skill/${skillId}`;

        // 以前のバリデーションエラーをクリア
        clearEditValidationErrors();
    });

    // フォーム送信時の処理 (Ajax)
    editSkillForm.addEventListener('submit', function (e) {
        e.preventDefault();

        clearEditValidationErrors();

        const formData = new FormData(this);
        const skillId = editSkillId.value;

        formData.append('_method', 'PUT');

        fetch(`/skill/${skillId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
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
                displayEditValidationErrors(error.errors);
            } else {
                alert('エラー: ' + error.message);
                console.error('Error:', error);
            }
        });
    });

    // テーブル行を更新する関数
    function updateTableRow(skillId, skill) {
        const row = document.getElementById(`skill-row-${skillId}`);
        if (row) {
            row.querySelector('td:nth-child(1)').innerHTML = `<strong>${skill.title}</strong>`;
            row.querySelector('td:nth-child(2)').textContent = skill.category;
            row.querySelector('td:nth-child(3)').textContent = skill.description.substring(0, 50) + (skill.description.length > 50 ? '...' : '');
            
            const imageCell = row.querySelector('td:nth-child(4)');
            if (skill.image_path) {
                let imageSrc = getImageSrc(skill.image_path);
                imageSrc += '?t=' + new Date().getTime(); 
                imageCell.innerHTML = `<img src="${imageSrc}" alt="${skill.title}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">`;
            } else {
                imageCell.innerHTML = 'なし';
            }

            // 編集ボタンのdata属性も更新
            const editButton = row.querySelector('.edit-skill-btn');
            if (editButton) {
                const dataImageSrc = skill.image_path ? getImageSrc(skill.image_path) : '';
                editButton.setAttribute('data-title', skill.title);
                editButton.setAttribute('data-category', skill.category);
                editButton.setAttribute('data-description', skill.description);
                editButton.setAttribute('data-image', dataImageSrc);
            }
        }
    }

    // 成功メッセージを表示する関数
    function showSuccessMessage(message) {
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
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertAdjacentElement('afterbegin', newAlert);
        } else {
            document.body.prepend(newAlert);
        }

        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(newAlert);
            alert.close();
        }, 3000);
    }

    // 編集モーダルのバリデーションエラーを表示する関数
    function displayEditValidationErrors(errors) {
        document.querySelectorAll('#editSkillForm .form-control, #editSkillForm .form-select').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#editSkillForm .invalid-feedback').forEach(el => el.textContent = '');

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

    // 編集モーダルのバリデーションエラーをクリアする関数
    function clearEditValidationErrors() {
        document.querySelectorAll('#editSkillForm .form-control, #editSkillForm .form-select').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#editSkillForm .invalid-feedback').forEach(el => el.textContent = '');
    }

    // ========== スキル削除の非同期化 ==========
    // 削除ボタンのイベントリスナーを設定（イベント委譲を使用）
    document.addEventListener('click', function(e) {
        // 削除ボタンまたはその子要素（アイコン）がクリックされた場合
        const deleteBtn = e.target.closest('.delete-skill-btn');
        if (deleteBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            const skillId = deleteBtn.getAttribute('data-skill-id');
            const skillTitle = deleteBtn.getAttribute('data-skill-title');
            
            // 確認ダイアログを表示
            if (confirm(`本当に「${skillTitle}」を削除しますか？`)) {
                deleteSkill(skillId, deleteBtn);
            }
        }
    });

    // スキル削除を実行する関数
    function deleteSkill(skillId, deleteBtn) {
        // ボタンを無効化してローディング状態にする
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>削除中...';

        // 正しい削除URLを構築
        const deleteUrl = `/skill/${skillId}/delete`;
        console.log('削除URL:', deleteUrl); // デバッグ用

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('サーバーからの応答が無効です。ページをリロードしてください。');
            }

            if (response.ok) {
                return response.json();
            } else {
                return response.json().then(data => {
                    throw new Error(data.message || '削除に失敗しました。');
                });
            }
        })
        .then(data => {
            if (data.success) {
                // テーブルから該当行を削除
                removeSkillFromTable(skillId);
                
                // 成功メッセージを表示
                showSuccessMessage(data.message);
                
            } else {
                alert(data.message || '削除に失敗しました。');
                // ボタンを元に戻す
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            alert('エラー: ' + error.message);
            console.error('Error:', error);
            // ボタンを元に戻す
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
    }

    // テーブルからスキル行を削除する関数
    function removeSkillFromTable(skillId) {
        const row = document.getElementById(`skill-row-${skillId}`);
        if (row) {
            // フェードアウトアニメーション
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';
            
            setTimeout(() => {
                row.remove();
                
                // テーブルが空になった場合の処理
                const skillsTableBody = document.getElementById('skillsTableBody');
                if (skillsTableBody && skillsTableBody.children.length === 0) {
                    const skillsTableContainer = document.getElementById('skillsTableContainer');
                    skillsTableContainer.innerHTML = `
                        <div class="alert alert-info" role="alert" id="noSkillsMessage">
                            まだ登録済みのスキルはありません。
                        </div>
                    `;
                }
            }, 300);
        }
    }
});
</script>
@endpush

{{-- ここにスタイルを追記します --}}
@push('styles')
<style>
/* あなたの新しい :root 変数定義をここに貼り付けます */
:root {
    --skillswap-primary:rgb(110, 161, 209); /* 新しいメインブルー（少し落ち着いた青） */
    --skillswap-primary-dark:rgb(121, 165, 203); /* メインカラーより濃い青（ホバー用やアクセントに） */
    --skillswap-text-light: #ffffff; /* 明るい背景用テキスト（白） */
    --skillswap-text-dark: #333333; /* 暗い背景用テキスト（濃いグレー） */
    --skillswap-bg-light: #f8f9fa; /* 薄い背景色 */
    --skillswap-border: #dee2e6; /* ボーダー色 */

    /* ステータス・警告に限定して使用する色（青と赤に集約） */
    --status-success: var(--skillswap-primary-dark); /* 承認ボタン: ブランドの濃い青 */
    --status-success-light: #C5E1F7; /* 承認バッジ/背景用: 薄い青 */

    --status-warning: #E26B6B; /* 申請取り消しボタン: 赤のバリエーション（少し明るめ） */
    --status-warning-dark: #CD5C5C;
    --status-warning-light: #F8D7DA; /* 申請取り消しバッジ/背景用: 薄い赤 */

    --status-danger:rgb(211, 103, 114); /* 拒否ボタン/警告: Bootstrapの赤に近い */
    --status-danger-dark: #c82333;
    --status-danger-light: #f8d7da; /* 薄い赤 */

    --status-info: #6c757d; /* 完了ボタン/その他情報ボタン: Bootstrapのミディアムグレー */
    --status-info-dark: #5a6268;
    --status-info-light: #e2e6ea; /* 完了バッジ/情報アラート背景用: 薄いグレー */

    /* 保留中の状態を示す色 */
    --status-pending: #6c757d; /* 保留中バッジ: Bootstrapのミディアムグレー */
    --status-pending-light: #e2e6ea; /* 保留中のバッジ背景用 */
}

/* 汎用的なリンクカラー */
a {
    color: var(--skillswap-primary-dark); /* リンクは濃い青 */
    text-decoration: none;
}
a:hover {
    color: var(--skillswap-primary); /* ホバー時はメインの青 */
    text-decoration: underline;
}

/* カードの基本的なスタイル */
.card {
    border-radius: 10px;
    border: 1px solid var(--skillswap-border);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.card-body {
    padding: 2.5rem;
}

/* --- カードヘッダーの色の調整 --- */
/* 全体的なカードヘッダーのデフォルト色（.card-header）を濃い青に */
.card-header {
    background-color: var(--skillswap-primary-dark) !important; /* 新しい濃い青 */
    color: var(--skillswap-text-light) !important;
    font-weight: bold;
    border-bottom: 1px solid var(--skillswap-primary-dark) !important;
}

/* ただし、警告カードのヘッダーは赤を保持 */
.card-header.bg-danger {
    background-color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
}

/* スキル管理ページの特定のカードヘッダーを上書き */
/* 新規スキル登録 (bg-primaryを使用) */
.card-header.bg-primary {
    background-color: var(--skillswap-primary-dark) !important; /* 新しい濃い青 */
    border-color: var(--skillswap-primary-dark) !important;
}

/* 登録済みスキル (bg-successを使用) */
.card-header.bg-success {
    background-color: var(--skillswap-primary-dark) !important; /* 新しい濃い青に統一 */
    border-color: var(--skillswap-primary-dark) !important;
}

/* スキルの一括登録 (bg-infoを使用) */
.card-header.bg-info {
    background-color: var(--status-info) !important; /* グレーに統一 */
    border-color: var(--status-info) !important;
}


/* 編集ボタン（カードヘッダー内のbtn-light） */
.card-header .btn-light {
    background-color: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    color: var(--skillswap-text-light) !important;
}
.card-header .btn-light:hover {
    background-color: rgba(255, 255, 255, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
}

/* --- モーダルの調整 --- */
.modal-header {
    /* 修正前: background-color: var(--skillswap-primary-dark); */
    background-color: var(--skillswap-primary)  !important;; /* スキル管理ページのカードヘッダーより少し明るい青に */
    color: var(--skillswap-text-light);
    border-bottom: 1px solid var(--skillswap-primary); /* ボーダーも合わせて調整 */
}
.modal-header .btn-close {
    filter: invert(1); /* 白いボタンアイコンを反転して見やすくする */
}

/* --- ボタンの調整 --- */
/* メインのアクションボタン (btn-primary) */
.btn-primary {
    background-color: var(--skillswap-primary); /* メインの青 */
    border-color: var(--skillswap-primary);
    color: var(--skillswap-text-light);
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-primary:hover {
    background-color: var(--skillswap-primary-dark); /* ホバー時は濃い青 */
    border-color: var(--skillswap-primary-dark);
    color: var(--skillswap-text-light);
}

/* btn-secondary の調整（落ち着いたグレー） */
.btn-secondary {
    background-color: var(--status-info); /* status-infoを共通のグレーとして使用 */
    border-color: var(--status-info);
    color: var(--skillswap-text-light);
}
.btn-secondary:hover {
    background-color: var(--status-info-dark);
    border-color: var(--status-info-dark);
}

/* btn-info の調整（グレー） */
.btn-info {
    background-color: var(--status-info); /* グレー */
    border-color: var(--status-info);
    color: var(--skillswap-text-light);
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-info:hover {
    background-color: var(--status-info-dark); /* ホバー時は少し濃いグレー */
    border-color: var(--status-info-dark);
}


/* Googleログインボタンは現状維持が良いでしょう */
.btn-google {
    background-color: #DB4437;
    border-color: #DB4437;
    color: var(--skillswap-text-light);
    transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.btn-google:hover {
    background-color: #C23326;
    border-color: #C23326;
    color: var(--skillswap-text-light);
}

/* --- アラート/メッセージの調整 --- */
/* セッション成功メッセージ (alert-success) */
.alert.alert-success {
    background-color: var(--status-success-light) !important; /* 薄い青 */
    border-color: var(--skillswap-primary) !important; /* メインの青のボーダー */
    color: var(--skillswap-text-dark) !important; /* 濃いめのテキスト */
}

/* バリデーションエラーメッセージ (alert-danger) */
.alert.alert-danger {
    background-color: var(--status-danger-light) !important; /* 薄い赤 */
    border-color: var(--status-danger) !important;
    color: #721c24 !important; /* Bootstrap dangerのテキスト色 */
}

/* まだ登録済みのスキルはありません (alert-info) */
.alert.alert-info {
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    border-color: var(--status-info) !important;
    color: var(--skillswap-text-dark) !important; /* 濃いめのテキスト */
    font-weight: bold;
}

/* 管理者からの警告カード内のアラート */
.card-body .alert-danger {
    background-color: var(--status-danger-light) !important; /* 薄い赤 */
    border-color: var(--status-danger) !important;
    color: #721c24 !important; /* Bootstrap dangerのテキスト色 */
}
.card-body .alert-danger .alert-heading {
    color: var(--status-danger) !important;
}
.card-body .btn-outline-danger:hover { /* ユーザー通報時のコメントのボタン */
    background-color: var(--status-danger) !important;
    color: var(--skillswap-text-light) !important;
}


/* --- スキル管理テーブル内のボタンの調整 --- */
/* スキル管理カード内のボタンはプライマリカラーに統一 */
/* 編集ボタン (btn-info) をプライマリカラーの青に */
.edit-skill-btn { /* HTMLで `btn btn-sm btn-info` となっているので、個別のクラスで上書き */
    background-color: var(--skillswap-primary) !important;
    border-color: var(--skillswap-primary) !important;
    color: var(--skillswap-text-light) !important;
}
.edit-skill-btn:hover {
    background-color: var(--skillswap-primary-dark) !important;
    border-color: var(--skillswap-primary-dark) !important;
}

/* 削除ボタン (btn-danger) は既存の赤を維持 */
.delete-skill-btn {
    background-color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
    color: var(--skillswap-text-light) !important;
}
.delete-skill-btn:hover {
    background-color: var(--status-danger-dark) !important;
    border-color: var(--status-danger-dark) !important;
}

/* スキルカードのホバーエフェクト（変更なし） */
.skill-card-link {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.skill-card-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
}


/* --- バッジの色の調整 --- */
/* ステータスが0（保留中）の場合のバッジスタイル */
.badge.bg-warning { /* 元々bg-warningが使われている箇所（ステータス0） */
    background-color: var(--status-pending) !important; /* 保留中はミディアムグレー */
    color: var(--skillswap-text-light) !important;
}
.badge.bg-success {
    background-color: var(--status-success-light) !important; /* 薄い青 */
    color: var(--skillswap-primary-dark) !important; /* テキストは濃い青 */
}
.badge.bg-danger {
    background-color: var(--status-danger-light) !important; /* 薄い赤 */
    color: var(--status-danger) !important;
}
/* ステータスが完了の場合、bg-infoを使用していると想定 */
.badge.bg-info {
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    color: var(--skillswap-text-dark) !important;
}

/* データエクスポートモーダルのボタンもプライマリカラーに統一 */
#exportHistoryModal .modal-footer .btn-primary {
    background-color: var(--skillswap-primary);
    border-color: var(--skillswap-primary);
    color: var(--skillswap-text-light);
}
#exportHistoryModal .modal-footer .btn-primary:hover {
    background-color: var(--skillswap-primary-dark);
    border-color: var(--skillswap-primary-dark);
}

/* HTMLからのテキスト色指定を調整 */
.text-primary { /* <span class="text-primary"> のスキル名 */
    color: var(--skillswap-primary-dark) !important; /* 濃い青 */
}

.text-success { /* <span class="text-success"> のユーザー名 */
    color: var(--skillswap-text-dark) !important; /* 濃いグレー */
}
</style>
@endpush