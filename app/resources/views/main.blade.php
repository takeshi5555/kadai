@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>メインページ</h1>
        <h2>スキルシェアサービスへようこそ！</h2>

        {{-- --- ここからボタンを追加 --- --}}
        <div class="main-cta-buttons">
            <a href="{{ url('/skill/search') }}" class="btn btn-primary">スキルを探す</a>

            @guest
                {{-- ログアウト中のユーザーにのみ表示 --}}
                <a href="{{ url('/signup') }}" class="btn btn-secondary">新規登録はこちら</a>
            @else
                {{-- ログイン中のユーザーにのみ表示 --}}
                <a href="{{ url('/skill/manage') }}" class="btn btn-success">スキルを教える</a>
            @endguest
        </div>
        {{-- --- ここまでボタンを追加 --- --}}


        <h3>おすすめカテゴリ</h3>
        @if($categories->isEmpty())
            <p>カテゴリはまだ登録されていません。</p>
        @else
            <div class="category-list"> {{-- 新しいCSSクラスを追加 --}}
                @foreach($categories as $category)
                    <a href="{{ url('/skill/search?category=' . urlencode($category)) }}" class="category-item"> {{-- ★カテゴリ検索へのリンク --}}
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        @endif


        <h2 class="mt-4">新着スキル</h2>
        @if($newSkills->isEmpty())
            <p>新着スキルはまだありません。</p>
        @else
            <div class="skill-grid">
                @foreach($newSkills as $skill)
                    {{-- ★ここを修正: skill-card 全体をリンク化し、内部の btn-primary を削除 --}}
                    <a href="{{ url('/skill/detail/' . $skill->id) }}" class="skill-card-link"> {{-- 新しいクラス `skill-card-link` を追加し、全体をリンクにする --}}
                        <div class="skill-card">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">{{ $skill->title }}</h3>
                                    <p class="card-text">
                                        <strong>カテゴリ:</strong> {{ $skill->category }}<br>
                                        <strong>提供者:</strong> {{ $skill->user->name ?? '不明なユーザー' }}
                                    </p>
                                    <p class="card-text">{{ Str::limit($skill->description, 100) }}</p>
                                    {{-- ★削除: 元の「詳細を見る」ボタン --}}
                                    {{-- <a href="{{ url('/skill/detail/' . $skill->id) }}" class="btn btn-primary">詳細を見る</a> --}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif


        <h2 class="mt-5">おすすめレビュー</h2>
        @if($featuredReviews->isEmpty())
            <p>まだレビューがありません。</p>
        @else
            <div class="featured-reviews">
                @foreach($featuredReviews as $review)
                    <div class="review-card">
                        <p class="skill-offered-info">
                            <strong>提供スキル：</strong>
                            <span class="skill-name-highlight">
                                {{ $review->matching->offeringSkill->title ?? '不明なスキル' }}
                            </span>
                        </p>
                        <p class="review-text">"{{ $review->comment }}"</p>
                        <div class="review-details">
                            <p class="review-author">
                                {{ $review->reviewer->name ?? '不明なレビュアー' }} さんから
                                {{ $review->reviewee->name ?? '不明な提供者' }} さんへ
                                - 評価: {{ $review->rating }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection


@push('styles')
<style>
.skill-card-link {
    text-decoration: none; /* 下線を削除 */
    color: inherit; /* テキストの色を親要素から継承 */
    display: block; /* aタグをブロック要素にして、全体がクリック可能に */
    height: 100%; /* 親要素に高さが指定されている場合に、子要素がそれを継承 */
}

.skill-card-link .skill-card .card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; /* ホバー時のアニメーション */
}

.skill-card-link:hover .skill-card .card {
    transform: translateY(-5px); /* 少し上に移動 */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* 影を強調 */
    cursor: pointer; /* カーソルをポインターにする */
}

/* 既存の skill-grid と skill-card のスタイルも調整して、見た目を整えると良いでしょう */
.skill-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* カードの最小幅と最大幅を調整 */
    gap: 20px; /* カード間のスペース */
}

.skill-card {
    /* リンクの高さに合わせてカードの高さも調整 */
    height: 100%;
}




</style>
@endpush