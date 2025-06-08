@extends('layouts.app') {{-- もしレイアウトを使用している場合 --}}

@section('content')
<div class="container py-4">
    <section class="mb-5">
        <h2 class="text-center mb-4">スキル検索結果</h2>

        {{-- 検索フォーム --}}
        <form action="{{ url('/skill/search') }}" method="GET" class="mb-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="keyword" placeholder="キーワード検索" value="{{ request('keyword') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="">全てのカテゴリ</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">検索</button>
                </div>
            </div>
        </form>

        @if($skills->isEmpty())
            <p class="text-center text-muted">該当するスキルは見つかりませんでした。</p>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> {{-- レスポンシブなグリッドレイアウト --}}
                @foreach($skills as $skill)
                    <div class="col">
                        <div class="card h-100 shadow-sm skill-card-link">
                            <a href="{{ url('/skill/detail/' . $skill->id) }}" class="text-decoration-none text-body">
                                {{-- スキル画像またはデフォルト画像を表示 --}}
                                @if($skill->image_path)
                                    <img src="{{ asset('storage/' . $skill->image_path) }}" 
                                         class="card-img-top" 
                                         alt="{{ $skill->title }}" 
                                         style="height: 180px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('images/categories/default.png') }}" 
                                         class="card-img-top" 
                                         alt="デフォルトスキル画像" 
                                         style="height: 180px; object-fit: cover;">
                                @endif
                                <div class="card-body">
                                    <h3 class="card-title h5 mb-2">{{ $skill->title }}</h3> {{-- タイトルサイズを調整 --}}
                                    <p class="card-text small text-muted mb-2">
                                        <strong>カテゴリ:</strong> {{ $skill->category }}<br>
                                        <strong>提供者:</strong> {{ $skill->user->name ?? '不明なユーザー' }}
                                    </p>
                                    <p class="card-text mb-0">{{ Str::limit($skill->description, 100) }}</p> {{-- 説明文の表示 --}}
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection

@push('styles')
<style>
/* カスタムCSS（public/css/app.css に追加すると良い） */


.skill-card-link {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.skill-card-link:hover {
    transform: translateY(-8px); /* ホバーで少し浮き上がる */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.7); /* ホバーで影を濃くする */
}


</style>
@endpush
