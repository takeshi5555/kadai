@extends('layouts.app')

@section('title', 'スキル検索')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4 text-center">スキル検索</h1>

        {{-- 検索フォーム --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">スキルを探す</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="/skill/search">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="keyword" class="form-label visually-hidden">キーワード</label>
                            <input type="text" class="form-control" id="keyword" name="keyword" placeholder="キーワードを入力" value="{{ request('keyword') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label visually-hidden">カテゴリ</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">カテゴリを選択</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">検索</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <h2 class="mb-3">検索結果</h2>
        @if ($skills->isEmpty())
            <div class="alert alert-info" role="alert">
                該当するスキルは見つかりませんでした。
            </div>
        @else
            <div class="list-group">
                @foreach ($skills as $skill)
                    <a href="/skill/detail/{{ $skill->id }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $skill->title }}</h5>
                            <small class="text-muted">{{ $skill->category }}</small>
                            <p class="mb-1">提供者: {{ $skill->user->name ?? '不明' }}</p>
                            <p class="text-secondary small mb-0">{{ Str::limit($skill->description, 150) }}</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection