@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
@php use Illuminate\Support\Str; @endphp

<div class="container" style="max-width: 960px; margin: 0 auto; padding: 20px;">
    <h1 class="mb-4">{{ $user->name }}さんのマイページ</h1>

    {{-- ユーザー情報カード --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">ユーザー情報</h2>
        </div>
        <div class="card-body">
            <p><strong>ユーザー名:</strong> {{ $user->name }}</p>
            <p><strong>メールアドレス:</strong> {{ $user->email }}</p>
            <a href="{{ route('password.request') }}" class="btn btn-secondary mt-2">
                パスワードを再設定する
            </a>
        </div>
    </div>

    {{-- 未読メッセージ通知 --}}
    @if($unreadMessagesCount > 0)
    <div class="alert alert-info mb-4" style="
        background-color: #e0f7fa;
        border-color: #00bcd4;
        color: #006064;
        padding: 15px;
        border-radius: 5px;
    ">
        <strong>新着メッセージがあります！</strong>
        <p class="mb-2">{{ $unreadMessagesCount }}件の未読メッセージがあります。</p>
        <a href="{{ route('matching.history.index') }}" class="btn btn-primary">
            メッセージ履歴を見る
        </a>
    </div>
    @endif

    {{-- 管理者からの警告カード --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-danger text-white">
            <h2 class="h5 mb-0">あなたへの管理者からの警告</h2>
        </div>
        <div class="card-body">
            @if($unreadWarnings->isEmpty() && $readWarnings->isEmpty())
                <p class="text-muted">現在、あなたへの警告はありません。</p>
            @else
                {{-- 未確認の警告表示 --}}
                @if($unreadWarnings->isNotEmpty())
                    <h4 class="h6 mb-3 text-danger">
                        未確認の警告 ({{ $unreadWarnings->count() }} 件)
                    </h4>
                    
                    @foreach($unreadWarnings as $warning)
                        <div class="alert alert-danger mb-3" role="alert">
                            <h5 class="alert-heading h6 mb-1">
                                新しい警告 ({{ ($warning->warned_at ?? $warning->created_at)->format('Y年m月d日 H:i') }})
                            </h5>
                            
                            <p class="mb-1">
                                <strong>管理者からのメッセージ:</strong> {{ $warning->message }}
                            </p>

                            @if($warning->report)
                                @if($warning->report->reason)
                                    <p class="mb-1">
                                        <strong>通報カテゴリ:</strong> {{ $warning->report->reason->reason_text }}
                                    </p>
                                @endif
                                
                                @if($warning->report->subReason)
                                    <p class="mb-1">
                                        <strong>通報詳細:</strong> {{ $warning->report->subReason->reason_text }}
                                    </p>
                                @endif
                                
                                @if($warning->report->comment)
                                    <p class="mb-1">
                                        <strong>ユーザー通報時のコメント:</strong> {{ $warning->report->comment }}
                                    </p>
                                @endif
                            @endif

                            <hr>
                            <form action="{{ route('warning.mark_as_read', $warning) }}" method="POST" class="text-end">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    この警告を確認済みにする
                                </button>
                            </form>
                        </div>
                    @endforeach
                @endif

                <p class="text-info small mt-3">
                    ※これらの警告は、過去の行為に対して管理者から通知されたものです。
                </p>
            @endif
        </div>
    </div>

    {{-- スキル管理カード --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <h2 class="h5 mb-0">私が提供できるスキル</h2>
        </div>
        <div class="card-body">
            @if($skills->isEmpty())
                <p class="text-muted">まだスキルを登録していません。</p>
                <a href="{{ route('skill.manage.index') }}" class="btn btn-success mt-2">
                    新しいスキルを登録する
                </a>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($skills as $skill)
                    <div class="col">
                        <a href="{{ route('skill.detail.show', $skill->id) }}" 
                           class="card h-100 text-decoration-none text-body skill-link">
                            <div class="card-body">
                                <h3 class="card-title h6 mb-2">
                                    {{ $skill->title }} ({{ $skill->category }})
                                </h3>
                                <p class="card-text text-muted small mb-2">
                                    {{ Str::limit($skill->description, 100) }}
                                </p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ route('skill.manage.index') }}" class="btn btn-success">
                        新しいスキルを登録・管理する
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- マッチング履歴カード --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">マッチング履歴</h2>
        </div>
        <div class="card-body">
            {{-- 申し込んだマッチング --}}
            <h4 class="mt-2 mb-3">あなたが申し込んだマッチング</h4>
            @if($requestedMatchings->isEmpty())
                <p class="text-muted">あなたが申し込んだマッチングはまだありません。</p>
            @else
                <div class="matching-list">
                    @foreach($requestedMatchings as $matching)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h3 class="h5">
                                    <span class="text-primary">{{ $matching->offeringSkill->title ?? 'N/A' }}</span>
                                    を提供する
                                    <span class="text-success">{{ $matching->offerUser->name ?? 'N/A' }}</span>
                                    さんへの申し込み
                                </h3>
                                
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <p><strong>ステータス:</strong> 
                                            <span class="badge bg-{{ $matching->status == 1 ? 'success' : ($matching->status == 3 ? 'danger' : 'warning') }}">
                                                {{ $matching->statusText }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>予定日時:</strong> 
                                            {{ $matching->scheduled_at ? $matching->scheduled_at->format('Y-m-d H:i') : '未定' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <p><strong>マッチングID:</strong> {{ $matching->id }}</p>

                                {{-- レビュー表示 --}}
                                @if($matching->status == 2)
                                    <div class="review-section mt-3">
                                        <p class="text-info">このマッチングは完了しています。</p>
                                        
                                        @if($matching->myReview)
                                            <div class="alert alert-primary">
                                                <strong>あなたのレビュー:</strong> 評価 {{ $matching->myReview->rating }} / 5<br>
                                                コメント: {{ $matching->myReview->comment ?? 'なし' }}
                                            </div>
                                        @else
                                            <a href="{{ route('review.form', ['matchingId' => $matching->id]) }}" 
                                               class="btn btn-success btn-sm">相手をレビューする</a>
                                        @endif
                                        
                                        @if($matching->reviewFromPartner)
                                            <div class="alert alert-success">
                                                <strong>相手からのレビュー:</strong> 評価 {{ $matching->reviewFromPartner->rating }} / 5<br>
                                                コメント: {{ $matching->reviewFromPartner->comment ?? 'なし' }}
                                            </div>
                                        @else
                                            <p class="text-muted">相手からのレビューはまだありません。</p>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted mt-3">マッチング完了後にレビューが表示されます。</p>
                                @endif

                                {{-- アクションボタン --}}
                                <div class="action-buttons mt-3">
                                    <a href="{{ route('message.show', ['matchingId' => $matching->id]) }}" 
                                       class="btn btn-primary btn-sm">メッセージを見る</a>
                                    
                                    @if($matching->status == 0 || $matching->status == 1)
                                        <form action="{{ route('matching.cancel', $matching->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm">キャンセル</button>
                                        </form>
                                    @endif
                                    
                                    @if($matching->status == 1)
                                        <form action="{{ route('matching.complete', $matching->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm">完了する</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 申し込まれたマッチング --}}
            <h4 class="mt-4 mb-3">相手から申し込まれたマッチング</h4>
            @if($offeredMatchings->isEmpty())
                <p class="text-muted">相手から申し込まれたマッチングはまだありません。</p>
            @else
                <div class="matching-list">
                    @foreach($offeredMatchings as $matching)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h3 class="h5">
                                    <span class="text-primary">{{ $matching->receivingSkill->title ?? 'N/A' }}</span>
                                    をリクエストする
                                    <span class="text-success">{{ $matching->requestUser->name ?? 'N/A' }}</span>
                                    さんからの申し込み
                                </h3>
                                
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <p><strong>ステータス:</strong> 
                                            <span class="badge bg-{{ $matching->status == 1 ? 'success' : ($matching->status == 3 ? 'danger' : 'warning') }}">
                                                {{ $matching->statusText }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>予定日時:</strong> 
                                            {{ $matching->scheduled_at ? $matching->scheduled_at->format('Y-m-d H:i') : '未定' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <p><strong>マッチングID:</strong> {{ $matching->id }}</p>

                                {{-- レビュー表示 --}}
                                @if($matching->status == 2)
                                    <div class="review-section mt-3">
                                        <p class="text-info">このマッチングは完了しています。</p>
                                        
                                        @if($matching->myReview)
                                            <div class="alert alert-primary">
                                                <strong>あなたのレビュー:</strong> 評価 {{ $matching->myReview->rating }} / 5<br>
                                                コメント: {{ $matching->myReview->comment ?? 'なし' }}
                                            </div>
                                        @else
                                            <a href="{{ route('review.form', ['matchingId' => $matching->id]) }}" 
                                               class="btn btn-success btn-sm mb-3">相手をレビューする</a>
                                        @endif
                                        
                                        @if($matching->reviewFromPartner)
                                            <div class="alert alert-success">
                                                <strong>相手からのレビュー:</strong> 評価 {{ $matching->reviewFromPartner->rating }} / 5<br>
                                                コメント: {{ $matching->reviewFromPartner->comment ?? 'なし' }}
                                            </div>
                                        @else
                                            <p class="text-muted">相手からのレビューはまだありません。</p>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted mt-3">マッチング完了後にレビューが表示されます。</p>
                                @endif

                                {{-- アクションボタン --}}
                                <div class="action-buttons mt-3">
                                    <a href="{{ route('message.show', ['matchingId' => $matching->id]) }}" 
                                       class="btn btn-primary btn-sm">メッセージを見る</a>
                                    
                                    @if($matching->status == 0)
                                        <form action="{{ route('matching.approve', $matching->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">承認する</button>
                                        </form>
                                        <form action="{{ route('matching.reject', $matching->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">拒否する</button>
                                        </form>
                                    @elseif($matching->status == 1)
                                        <form action="{{ route('matching.complete', $matching->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm">完了する</button>
                                        </form>
                                        <form action="{{ route('matching.cancel', $matching->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm">キャンセル</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <div class="text-center mt-4">
                <a href="{{ route('matching.history.index') }}" class="btn btn-info">
                    全マッチング履歴を見る
                </a>
            </div>
        </div>
    </div>

    {{-- データエクスポートカード --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h2 class="h5 mb-0">
                <i class="fas fa-download me-2"></i>データエクスポート
            </h2>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                期間を指定してマッチング履歴をCSV形式でエクスポートします。就職活動や学習の振り返りにご活用ください。
            </p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportHistoryModal">
                <i class="fas fa-file-export me-2"></i>履歴をエクスポート
            </button>
        </div>
    </div>

    {{-- エクスポートモーダル --}}
    <div class="modal fade" id="exportHistoryModal" tabindex="-1" aria-labelledby="exportHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportHistoryModalLabel">
                        マッチング履歴のエクスポート設定
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form method="POST" action="{{ route('profile.export.execute') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">開始日 (任意):</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_date" class="form-label">終了日 (任意):</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ステータスで絞り込む (任意):</label>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="0" id="statusPending" checked>
                                <label class="form-check-label" for="statusPending">申請中</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="1" id="statusApproved" checked>
                                <label class="form-check-label" for="statusApproved">承認済み</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="2" id="statusCompleted" checked>
                                <label class="form-check-label" for="statusCompleted">完了</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="3" id="statusRejected">
                                <label class="form-check-label" for="statusRejected">拒否</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                        <button type="submit" class="btn btn-primary">エクスポート</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* スキルカードのホバーエフェクト */
.skill-link {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.skill-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* カードの基本設定 */
.container .card {
    max-width: 100%;
    box-sizing: border-box;
}

/* テキストの折り返し */
.card-body p {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* ボタンの最適化 */
.card-body .btn {
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* マッチングリストのスタイル */
.matching-list .card {
    border-left: 4px solid #007bff;
}

/* アクションボタンの間隔 */
.action-buttons .btn {
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

/* レビューセクションのスタイル */
.review-section .alert {
    margin-bottom: 0.5rem;
}

/* ステータスバッジのスタイル調整 */
.badge {
    font-size: 0.875em;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .action-buttons .btn {
        width: 100%;
        margin-right: 0;
    }
    
    .row .col-md-6 {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush