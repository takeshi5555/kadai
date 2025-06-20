@extends('layouts.app') {{-- layouts/app.blade.php を継承 --}}

@section('title', 'インポート内容確認') {{-- このページのタイトルを設定 --}}

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-11"> {{-- 編集欄が増えるため、さらに広めのカラムに変更推奨 --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0 text-center">インポート内容確認</h1>
                    </div>
                    <div class="card-body">
                        {{-- フラッシュメッセージ（セッションに'error'がある場合） --}}
                        @if (session('error'))
                            <div class="alert alert-danger text-center" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @php
                            // セッションからエラーフラグを取得
                            $hasError = Session::get('import_has_error', false);
                        @endphp

                        @if ($hasError)
                            <div class="alert alert-warning text-center mb-3" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>一部の行にエラーがあります。**インポート前にこの画面で修正するか、不要な行を削除してください。** 修正または削除しない場合、エラーのある行はインポートされません。
                            </div>
                        @endif

                        @if (empty($skills))
                            <div class="alert alert-info text-center" role="alert">
                                インポートするスキルデータがありません。
                            </div>
                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <a href="{{ url('/skill/import') }}" class="btn btn-secondary btn-lg">ファイル再選択</a>
                            </div>
                        @else
                            <form method="POST" action="{{ url('/skill/import/execute') }}" id="importForm">
                                @csrf
                                <div class="table-responsive"> {{-- テーブルが横にはみ出す場合にスクロール可能にする --}}
                                    <table class="table table-bordered table-hover align-middle"> {{-- align-middleで垂直方向中央揃え --}}
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>タイトル</th> {{-- 編集可能に --}}
                                                <th>CSVからのカテゴリ</th>
                                                <th>登録するカテゴリ</th>
                                                <th>説明</th> {{-- 編集可能に --}}
                                                <th>ステータス</th>
                                                <th>削除</th> {{-- 削除ボタンの列を追加 --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($skills as $index => $skill)
                                                {{-- エラーがある行は背景色を変更 --}}
                                                <tr class="skill-row {{ $skill['error'] ? 'table-danger' : '' }}" data-index="{{ $index }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <input type="text" 
                                                               name="title[{{ $index }}]" 
                                                               class="form-control {{ $errors->has('title.' . $index) ? 'is-invalid' : '' }}" 
                                                               value="{{ old('title.' . $index, $skill['title']) }}" 
                                                               required>
                                                        @error('title.' . $index)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        @if ($skill['original_category'])
                                                            {{ $skill['original_category'] }}
                                                        @else
                                                            <span class="text-muted">(空)</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{-- カテゴリ選択プルダウン --}}
                                                        <select 
                                                            class="form-select skill-category-select {{ $errors->has('category.' . $index) ? 'is-invalid' : '' }}" 
                                                            name="category[{{ $index }}]" 
                                                            required
                                                        >
                                                            <option value="">カテゴリを選択</option>
                                                            @foreach ($existingCategories as $cat)
                                                                <option 
                                                                    value="{{ $cat }}" 
                                                                    @if (old('category.' . $index, $skill['original_category']) === $cat) selected @endif
                                                                >
                                                                    {{ $cat }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('category.' . $index)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <textarea name="description[{{ $index }}]" 
                                                                  class="form-control {{ $errors->has('description.' . $index) ? 'is-invalid' : '' }}" 
                                                                  rows="3">{{ old('description.' . $index, $skill['description']) }}</textarea>
                                                        @error('description.' . $index)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        @if ($skill['error'])
                                                            <span class="text-danger fw-bold">{{ $skill['error'] }}</span>
                                                        @else
                                                            OK
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm delete-skill-row" data-index="{{ $index }}">
                                                            <i class="fas fa-trash-alt"></i> 削除
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div> {{-- .table-responsive 終了 --}}

                                <div class="d-flex justify-content-center gap-3 mt-4"> {{-- ボタンを中央に配置し、隙間を設ける --}}
                                    <button type="submit" class="btn btn-primary btn-lg">インポート実行</button>
                                    <a href="{{ url('/skill/manage') }}" class="btn btn-secondary btn-lg">ファイル再選択</a>
                                </div>
                            </form>
                        @endif
                    </div> {{-- .card-body 終了 --}}
                </div> {{-- .card 終了 --}}
            </div> {{-- .col-md-11 終了 --}}
        </div> {{-- .row 終了 --}}
    </div> {{-- .container 終了 --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    const deleteButtons = document.querySelectorAll('.delete-skill-row');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const indexToDelete = this.dataset.index;
            const rowToDelete = document.querySelector(`.skill-row[data-index="${indexToDelete}"]`);
            
            if (rowToDelete) {
                rowToDelete.style.display = 'none';
                
                // その行に含まれるすべてのフォーム要素（input, select, textarea）を無効にする
                // 無効にすることで、フォーム送信時にその要素の値が送信されなくなる
                const formElements = rowToDelete.querySelectorAll('input, select, textarea');
                formElements.forEach(element => {
                    element.disabled = true; // disabled属性を設定
                });
            }
        });
    });
    importForm.addEventListener('submit', function(event) {
    });
});
</script>
@endpush