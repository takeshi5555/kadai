@extends('layouts.app')

@section('title', 'スキル詳細')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0">スキル詳細</h1>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-3"><strong>スキル名：</strong> {{ $skill->title }}</h5>
                        <p class="card-text mb-2"><strong>カテゴリ：</strong> {{ $skill->category }}</p>
                        <p class="card-text mb-3"><strong>説明：</strong> {!! nl2br(e($skill->description)) !!}</p>

                        <hr class="my-4">

                        <h5>このスキルの統計</h5>
                        <ul class="list-unstyled mb-3">
                            <li><strong>マッチング件数：</strong> {{ $skillMatchingCount }}件</li>
                            {{-- 注: スキル直接の評価平均は Review テーブル構造から直接取得できないため、
                                 ここでは提供者へのレビュー平均を表示します。 --}}
                            <li><strong>提供者への評価平均：</strong>
                                @if ($userAverageRating)
                                    {{ number_format($userAverageRating, 1) }}
                                @else
                                    まだ評価はありません
                                @endif
                            </li>
                        </ul>

                        <div class="d-grid gap-2 mt-4">
                            <a href="/matching/apply/{{ $skill->id }}" class="btn btn-success btn-lg">マッチングを申し込む</a>
                            <a href="{{ route('skill.search') }}" class="btn btn-secondary btn-lg">スキル検索に戻る</a>
                        </div>
                    </div>
                </div>

                {{-- スキル提供者情報 --}}
                @if ($skill->user)
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">スキル提供者情報</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><strong>提供者名：</strong> {{ $skill->user->name }}</p>
                            <ul class="list-unstyled mb-3">
                                <li><strong>総マッチング件数：</strong> {{ $userTotalMatchingCount }}件</li>
                                <li><strong>全レビューの評価平均：</strong>
                                    @if ($userAverageRating)
                                        {{ number_format($userAverageRating, 1) }}
                                    @else
                                        まだ評価はありません
                                    @endif
                                </li>
                            </ul>

                            @if ($userLatestReviews->isNotEmpty())
                                <h6>提供者への最新レビュー</h6>
                                <ul class="list-group">
                                    @foreach ($userLatestReviews as $review)
                                        <li class="list-group-item">
                                            評価：{{ $review->rating }}
                                            <p class="mb-0">{{ $review->comment }}</p>
                                            <small class="text-muted">{{ $review->created_at->format('Y/m/d H:i') }}</small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="alert alert-info" role="alert">
                                    この提供者へのレビューはまだありません。
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
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