@extends('layouts.app')

@section('title', 'マッチング履歴')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="mb-4 text-center">マッチング履歴</h1>

                {{-- あなたが申請したマッチング --}}
<h2 class="mb-3">あなたが申請したマッチング</h2>
@forelse ($applied as $match)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                相手：{{ $match->receivingSkill->user->name ?? '不明' }}
            </h5>
            <span class="badge bg-{{
                match ($match->status) {
                    0 => 'warning', // 申請中
                    1 => 'success', // 承認済み
                    2 => 'info',    // 完了
                    3 => 'danger',  // キャンセル
                    4 => 'danger',  // 拒否
                    default => 'secondary' // その他のステータス
                }
            }} text-dark fs-6">
                @switch($match->status)
                    @case(0) 申請中 @break
                    @case(1) 承認済み @break
                    @case(2) 完了 @break
                    @case(3) キャンセル @break
                    @case(4) 拒否 @break
                @endswitch
            </span>
        </div>
        <div class="card-body">
            <p class="card-text mb-1"><strong>あなたが提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p>
            <p class="card-text mb-1"><strong>相手が提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p>
            <p class="card-text mb-3"><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H時i分') : '未定' }}</p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                @if ($match->status === 0)
                    <form method="POST" action="/matching/{{ $match->id }}/cancel" onsubmit="return confirm('本当にこの申請を取り消しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">申請取り消し</button>
                    </form>
                @endif

                @if ($match->status === 1)
                    <form method="POST" action="/matching/{{ $match->id }}/complete" onsubmit="return confirm('このマッチングを完了しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">完了</button>
                    </form>
                @endif

                {{-- ★ここを修正: statusが0, 1, 2の時にメッセージボタンを表示する★ --}}
                @if (in_array($match->status, [0, 1, 2]))
                    <a href="/message/{{ $match->id }}" class="d-inline-block">
                        <button type="button" class="btn btn-outline-primary btn-sm">メッセージ</button>
                    </a>
                @endif
            </div>

            @if ($match->status === 2)
                <div class="row mt-3">
                    <div class="col-md-6">
                        @if ($match->myReview)
                            <div class="card bg-light border-primary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">あなたのレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->myReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->myReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <a href="/review/{{ $match->id }}" class="btn btn-success btn-sm">レビューを書く</a>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if ($match->partnerReview)
                            <div class="card bg-light border-secondary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">相手のレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->partnerReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->partnerReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary py-2 px-3 mb-0" role="alert">
                                相手からのレビューはまだありません。
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info" role="alert">
        申請したマッチングはありません。
    </div>
@endforelse

---

{{-- あなたに申請されたマッチング --}}
<h2 class="mb-3">あなたに申請されたマッチング</h2>
@forelse ($received as $match)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                相手：{{ $match->offeringSkill->user->name ?? '不明' }}
            </h5>
            <span class="badge bg-{{
                match ($match->status) {
                    0 => 'warning', // 申請中
                    1 => 'success', // 承認済み
                    2 => 'info',    // 完了
                    3 => 'danger',  // 拒否 (※以前のコンテキストで4は表示しないようにしたため、ここでは考慮不要)
                    default => 'secondary' // その他のステータス
                }
            }} text-dark fs-6">
                @switch($match->status)
                    @case(0) 申請中 @break
                    @case(1) 承認済み @break
                    @case(2) 完了 @break
                    @case(3) 拒否 @break {{-- 実際には表示されないが、念のため記述 --}}
                @endswitch
            </span>
        </div>
        <div class="card-body">
            <p class="card-text mb-1"><strong>あなたが提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p>
            <p class="card-text mb-1"><strong>相手が提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p>
            <p class="card-text mb-3"><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H時i分') : '未定' }}</p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                @if ($match->status === 0)
                    <form method="POST" action="/matching/{{ $match->id }}/approve" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">承認</button>
                    </form>
                    <form method="POST" action="/matching/{{ $match->id }}/reject" onsubmit="return confirm('本当にこの申請を拒否しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">拒否</button>
                    </form>
                @endif

                @if ($match->status === 1)
                    <form method="POST" action="/matching/{{ $match->id }}/complete" onsubmit="return confirm('このマッチングを完了しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">完了</button>
                    </form>
                @endif

                {{-- ★ここを修正: statusが0, 1, 2の時にメッセージボタンを表示する★ --}}
                @if (in_array($match->status, [0, 1, 2]))
                    <a href="/message/{{ $match->id }}" class="d-inline-block">
                        <button type="button" class="btn btn-outline-primary btn-sm">メッセージ</button>
                    </a>
                @endif
            </div>

            @if ($match->status === 2)
                <div class="row mt-3">
                    <div class="col-md-6">
                        @if ($match->myReview)
                            <div class="card bg-light border-primary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">あなたのレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->myReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->myReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <a href="/review/{{ $match->id }}" class="btn btn-success btn-sm">レビューを書く</a>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if ($match->partnerReview)
                            <div class="card bg-light border-secondary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">相手のレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->partnerReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->partnerReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary py-2 px-3 mb-0" role="alert">
                                相手からのレビューはまだありません。
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info" role="alert">
        申請されたマッチングはありません。
    </div>
@endforelse
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
        min-height: 150px; 

    }

    .skill-provider-card {
        min-height: 150px;
    }

</style>