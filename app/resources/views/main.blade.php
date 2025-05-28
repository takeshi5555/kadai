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
            <div class="skill-grid"> {{-- ★このクラスを追加 --}}
                @foreach($newSkills as $skill)
                    <div class="skill-card"> {{-- ★このクラスを追加 --}}
                        <div class="card"> {{-- あなたのcardクラスはそのまま --}}
                            <div class="card-body">
                                <h3 class="card-title">{{ $skill->title }}</h3>
                                <p class="card-text">
                                    <strong>カテゴリ:</strong> {{ $skill->category }}<br>
                                    <strong>提供者:</strong> {{ $skill->user->name ?? '不明なユーザー' }}
                                </p>
                                <p class="card-text">{{ Str::limit($skill->description, 100) }}</p>
                                <a href="{{ url('/skill/detail/' . $skill->id) }}" class="btn btn-primary">詳細を見る</a>
                            </div>
                        </div>
                    </div>
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
                {{-- 提供スキル名を表示する新しい行 --}}
                <p class="skill-offered-info">
                    <strong>提供スキル：</strong>
                    <span class="skill-name-highlight">
                        {{ $review->matching->offeringSkill->title ?? '不明なスキル' }}
                    </span>
                </p>

                {{-- レビューコメントはそのまま --}}
                <p class="review-text">"{{ $review->comment }}"</p>

                <div class="review-details">
                    <p class="review-author">
                        {{-- レビューを書いた人 (reviewer) の名前 --}}
                        {{ $review->reviewer->name ?? '不明なレビュアー' }} さんから
                        {{-- レビューされた人 (reviewee) の名前 --}}
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