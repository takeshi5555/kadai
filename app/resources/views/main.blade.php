@extends('layouts.app')

@section('content')
@php use Illuminate\Support\Str; @endphp {{-- Str::limit を使うため、ここでuse宣言 --}}

<div class="container py-5"> {{-- 全体的な上下のパディングを大きく --}}
    <h1 class="text-center mb-3">SkillSwap</h1> {{-- タイトルを中央寄せに --}}
    <p class="lead text-center mb-5">スキルシェアサービスへようこそ！</p> {{-- リード文として少し大きく、中央寄せに --}}

    {{-- --- 主要CTAボタン --- --}}
    <div class="d-grid gap-3 col-md-8 col-lg-6 mx-auto mb-5"> {{-- ボタンを中央に配置し、適切な幅に --}}
        <a href="{{ url('/skill/search') }}" class="btn btn-primary btn-lg">スキルを探す</a>

        @guest
            <a href="{{ url('/signup') }}" class="btn btn-outline-secondary btn-lg">新規登録はこちら</a>
        @else
            <a href="{{ url('/skill/manage') }}" class="btn btn-success btn-lg">スキルを教える</a>
        @endguest
    </div>
    {{-- --- 主要CTAボタンここまで --- --}}

    <hr class="my-5"> {{-- セクション間の明確な区切り --}}

       <section class="mb-5 text-center bg-light p-5 rounded">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <h2 class="mb-4 display-5 fw-bold">SkillSwapで、あなたの世界を広げよう。</h2>
                <p class="lead mb-4">SkillSwapは、あなたが持っているスキルを他の誰かに教えたり、逆に学びたいスキルを持つ人から学ぶことができる、スキルシェアサービスです。</p>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-person-check fs-2 text-primary"></i> {{-- Bootstrap Icons を使う場合 --}}
                        <h4 class="mt-2">簡単登録</h4>
                        <p class="text-muted">数ステップであなたのスキルを登録できます。</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-book fs-2 text-success"></i>
                        <h4 class="mt-2">多様なスキル</h4>
                        <p class="text-muted">プログラミングから料理まで、あらゆるスキルが集まります。</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-hand-thumbs-up fs-2 text-info"></i>
                        <h4 class="mt-2">安心サポート</h4>
                        <p class="text-muted">安全なマッチングとサポート体制で安心。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- --- おすすめカテゴリ --- --}}
    <section class="mb-5">
        <h2 class="text-center mb-4">おすすめカテゴリ</h2>
        @if($categories->isEmpty())
            <p class="text-center text-muted">カテゴリはまだ登録されていません。</p>
        @else
            <div class="d-flex flex-wrap justify-content-center gap-3"> {{-- カテゴリをフレックスボックスで中央寄せに --}}
                @foreach($categories as $category)
                    <a href="{{ url('/skill/search?category=' . urlencode($category)) }}" class="btn btn-outline-info text-nowrap"> {{-- ボタン風のカテゴリ表示 --}}
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        @endif
    </section>
    {{-- --- おすすめカテゴリここまで --- --}}

    <hr class="my-5">

    {{-- --- 新着スキル --- --}}
    <section class="mb-5">
        <h2 class="text-center mb-4">新着スキル</h2>
        @if($newSkills->isEmpty())
            <p class="text-center text-muted">新着スキルはまだありません。</p>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> {{-- レスポンシブなグリッドレイアウト --}}
                @foreach($newSkills as $skill)
                    <div class="col">
                        <a href="{{ url('/skill/detail/' . $skill->id) }}" class="card h-100 shadow-sm text-decoration-none text-body skill-card-link"> {{-- カード全体をリンクに --}}
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
                @endforeach
            </div>
        @endif
    </section>
    {{-- --- 新着スキルここまで --- --}}

    <hr class="my-5">

    {{-- --- おすすめレビュー --- --}}
    <section class="mb-5">
        <h2 class="text-center mb-4">おすすめレビュー</h2>
        @if($featuredReviews->isEmpty())
            <p class="text-center text-muted">まだレビューがありません。</p>
        @else
            <div class="row row-cols-1 row-cols-md-2 g-4"> {{-- レビューもグリッドレイアウトで --}}
                @foreach($featuredReviews as $review)
                    <div class="col">
                        <div class="card h-100 shadow-sm review-card"> {{-- レビューカードのデザイン --}}
                            <div class="card-body">
                                <p class="card-text text-info mb-1">
                                    <strong>提供スキル：</strong>
                                    <span class="fw-bold">{{ $review->matching->offeringSkill->title ?? '不明なスキル' }}</span>
                                </p>
                                <p class="card-text fs-5 fw-bold mb-3">"{{ $review->comment }}"</p> {{-- コメントを強調 --}}
                                <div class="text-end">
                                    <p class="card-text text-muted small mb-0">
                                        {{ $review->reviewer->name ?? '不明なレビュアー' }} さんから
                                        {{ $review->reviewee->name ?? '不明な提供者' }} さんへ
                                    </p>
                                    <p class="card-text text-muted small">
                                        評価: <span class="fw-bold text-warning">{{ $review->rating }}</span>
                                        {{-- ここに星アイコンなどを追加するとより見栄えが良いです --}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
    {{-- --- おすすめレビューここまで --- --}}

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