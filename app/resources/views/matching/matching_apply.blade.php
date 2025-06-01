@extends('layouts.app')

@section('title', 'マッチング申し込み')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4 text-center">マッチング申し込み</h1>

                {{-- 相手のスキル情報と提供者情報 --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">相手のスキルと提供者情報</h5> {{-- ヘッダーのタイトルを修正 --}}
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><strong>スキル名：</strong> {{ $targetSkill->title }}</h5>
                        <p class="card-text mb-2"><strong>カテゴリ：</strong> {{ $targetSkill->category }}</p>
                        <p class="card-text mb-3"><strong>説明：</strong> {!! nl2br(e($targetSkill->description)) !!}</p>

                        <hr class="my-3"> {{-- 区切り線 --}}

                        <h6>スキル提供者： {{ $targetSkill->user->name }}</h6> {{-- 提供者名を小さい見出しで表示 --}}
                        <ul class="list-unstyled mb-3">
                            <li><strong>総マッチング件数：</strong> {{ $targetUserTotalMatchingCount }}件</li>
                            <li><strong>全レビューの評価平均：</strong>
                                @if ($targetUserAverageRating)
                                    {{ number_format($targetUserAverageRating, 1) }}
                                @else
                                    まだ評価はありません
                                @endif
                            </li>
                        </ul>

                        @if ($targetUserLatestReviews->isNotEmpty())
                            <h6>最新レビュー</h6>
                            <ul class="list-group mb-3">
                                @foreach ($targetUserLatestReviews as $review)
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

                {{-- 自分のスキル選択フォーム（変更なし） --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">自分のスキルを選択し、日時を設定</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/matching/apply/confirm">
                            @csrf
                            <input type="hidden" name="receiving_skill_id" value="{{ $targetSkill->id }}">

                            <div class="mb-3">
                                <label for="offering_skill_id" class="form-label">提供するスキル:</label>
                                <select name="offering_skill_id" id="offering_skill_id" class="form-select" required>
                                    <option value="">選択してください</option>
                                    @foreach ($mySkills as $skill)
                                        <option value="{{ $skill->id }}">{{ $skill->title }}（{{ $skill->category }}）</option>
                                    @endforeach
                                </select>
                                @error('offering_skill_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="scheduled_at" class="form-label">日時を選択:</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" required>
                                @error('scheduled_at')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">確認画面へ</button>
                                <a href="/skill/detail/{{ $targetSkill->id }}" class="btn btn-secondary btn-lg">キャンセルして戻る</a>
                            </div>
                        </form>
                    </div>
                </div>
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