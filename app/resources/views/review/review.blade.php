@extends('layouts.app') {{-- layouts/app.blade.php を継承 --}}

@section('title', 'レビュー投稿') {{-- ページのタイトルを設定 --}}

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0 text-center">レビュー投稿</h1>
                    </div>
                    <div class="card-body">
                        <p class="mb-4 text-center">
                            <strong>対象スキル:</strong>
                            <span class="badge bg-info text-dark me-1">{{ $matching->offeringSkill->title }}</span>
                            <i class="bi bi-arrow-left-right"></i>
                            <span class="badge bg-info text-dark ms-1">{{ $matching->receivingSkill->title }}</span>
                        </p>

                        <form method="POST" action="{{ url('/review/' . $matching->id) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="rating" class="form-label d-block text-center">評価 (1〜5):</label>
                                <div class="d-flex justify-content-center">
                                    <select name="rating" id="rating" class="form-select w-auto" required>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @if($i == 5) selected @endif>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @error('rating')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="comment" class="form-label">コメント:</label>
                                <textarea name="comment" id="comment" class="form-control" rows="5" maxlength="1000" placeholder="レビューコメントを入力してください"></textarea>
                                @error('comment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 col-6 mx-auto">
                                <button type="submit" class="btn btn-primary btn-lg">送信</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div> 
    </div>
@endsection