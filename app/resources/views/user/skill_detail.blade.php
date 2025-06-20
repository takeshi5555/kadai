@extends('layouts.app')

@section('title', 'スキル詳細')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                {{-- スキル詳細情報カード --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0">スキル詳細</h1>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-3"><strong>スキル名：</strong> {{ $skill->title }}</h5>
                        <p class="card-text mb-2"><strong>カテゴリ：</strong> {{ $skill->category }}</p>
                        <p class="card-text mb-3"><strong>説明：</strong> {!! nl2br(e($skill->description)) !!}</p>

                        {{-- 画像表示の追加（もしあれば） --}}
                        @if($skill->image_path)
                            <div class="mb-3">
                                <img src="{{ Storage::url($skill->image_path) }}" alt="{{ $skill->title }}" class="img-fluid rounded" style="max-width: 300px;">
                            </div>
                        @endif

                        <hr class="my-4">

                        <h5>このスキルの統計</h5>
                        <ul class="list-unstyled mb-3">
                            <li><strong>マッチング件数：</strong> {{ $skillMatchingCount }}件</li>
                            <li><strong>このスキルに対する評価平均：</strong>
                                @if ($skillAverageRating)
                                    {{ number_format($skillAverageRating, 1) }}
                                @else
                                    まだ評価はありません
                                @endif
                            </li>
                        </ul>

                        {{-- このスキルへのレビュー表示セクション --}}
                        <hr class="my-4">
                        <h5>このスキルへのレビュー</h5>
                        @if ($skillReviews->isNotEmpty())
                            <ul class="list-group mb-3">
                                @foreach ($skillReviews as $review)
                                    <li class="list-group-item">
                                        <strong>評価：{{ $review->rating }}</strong>
                                        <p class="mb-0">{{ $review->comment }}</p>
                                        <small class="text-muted">
                                            {{ $review->reviewerUser->name ?? '匿名ユーザー' }} さんより
                                            ({{ $review->created_at->format('Y/m/d H:i') }})
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="alert alert-info" role="alert">
                                このスキルへのレビューはまだありません。
                            </div>
                        @endif

                        {{-- マッチング申し込み/マイページへのリンクボタン --}}
                        <div class="d-grid gap-2 mt-4">
                            @auth {{-- ログインしている場合 --}}
                                @if (Auth::id() === $skill->user_id) {{-- ログインユーザーがこのスキルの提供者である場合 --}}
                                    <div class="alert alert-info text-center" role="alert">
                                        これはあなたが提供しているスキルです。
                                    </div>
                                    <a href="{{ route('mypage.index') }}" class="btn btn-secondary btn-lg">マイページに戻る</a>
                                @else {{-- ログインユーザーがこのスキルの提供者ではない場合 --}}
                                    <a href="/matching/apply/{{ $skill->id }}" class="btn btn-success btn-lg">マッチングを申し込む</a>
                                    <a href="{{ route('skill.search') }}" class="btn btn-secondary btn-lg">スキル検索に戻る</a>
                                @endif
                            @else {{-- ログインしていない場合 --}}
                                <div class="alert alert-warning text-center" role="alert">
                                    マッチングを申し込むには<a href="{{ route('login') }}">ログイン</a>してください。
                                </div>
                                <a href="{{ route('skill.search') }}" class="btn btn-secondary btn-lg">スキル検索に戻る</a>
                            @endauth
                        </div>
                    </div> {{-- .card-body --}}
                </div> {{-- .card.shadow-sm.mb-4 (スキル詳細カード) --}}

                {{-- スキル提供者情報カード --}}
                @if ($skill->user)
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">スキル提供者情報</h5>
                            @auth {{-- ログインしている場合のみ通報ボタンを表示 --}}
                                @if (Auth::id() !== $skill->user->id) {{-- 自分のスキルではない場合のみ通報ボタンを表示 --}}
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal"
                                        data-reportable-type="App\Models\User"
                                        data-reportable-id="{{ $skill->user->id }}"
                                        data-reported-user-id="{{ $skill->user->id }}">
                                        <i class="bi bi-flag me-1"></i> {{ $skill->user->name }}さんを通報
                                    </button>
                                @endif
                            @endauth
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

                            {{-- 提供者の他のスキルを表示するセクション --}}
                            @if ($otherUserSkills->isNotEmpty())
                                <h6 class="mt-4 mb-2">この提供者の他のスキル</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach ($otherUserSkills as $otherSkill)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <a href="{{ route('skill.detail.show', $otherSkill->id) }}" class="text-decoration-none">
                                                {{ $otherSkill->title }} <small class="text-muted">({{ $otherSkill->category }})</small>
                                            </a>
                                            <i class="bi bi-arrow-right-circle text-primary"></i>
                                        </li>
                                    @endforeach
                                </ul>
                                {{-- 全スキルへのリンクが必要なら追加する場合の例 --}}
                                {{-- <div class="text-end mt-2">
                                    <a href="{{ route('user.profile', $skill->user->id) }}" class="btn btn-sm btn-outline-secondary">
                                        {{ $skill->user->name }}さんの全スキルを見る
                                    </a>
                                </div> --}}
                            @else
                                <div class="alert alert-info mt-4" role="alert">
                                    この提供者の他のスキルはまだ登録されていません。
                                </div>
                            @endif
                        </div> {{-- .card-body --}}
                    </div> {{-- .card.shadow-sm.mt-4 (スキル提供者情報カード) --}}
                @endif {{-- @if ($skill->user) の閉じタグ --}}

            </div> {{-- .col-md-8 --}}
        </div> {{-- .row.justify-content-center --}}
    </div> {{-- .container.py-4 --}}

{{-- 通報モーダルのHTMLは変更なし --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reportForm" method="POST" action="{{ route('reports.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">コンテンツを通報する</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="reportable_type" id="reportable_type">
                    <input type="hidden" name="reportable_id" id="reportable_id">
                    <input type="hidden" name="reported_user_id" id="reported_user_id">

                    <div class="mb-3">
                        <label for="reason_id" class="form-label">通報理由（大まかな選択）</label>
                        <select class="form-select" id="reason_id" name="reason_id" required>
                            <option value="">選択してください</option>
                            @foreach(\App\Models\ReportReason::topLevel()->get() as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->reason_text }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="sub_reason_container" style="display: none;">
                        <label for="sub_reason_id" class="form-label">詳細な理由</label>
                        <select class="form-select" id="sub_reason_id" name="sub_reason_id">
                            <option value="">選択してください</option>
                        </select>
                        <div id="loadingSubReasons" style="display: none; margin-top: 5px;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>読み込み中...</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">具体的な状況を記入してください (任意)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger" id="submitReportButton" style="display: none;">通報を送信</button>
                </div>
            </form>
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

{{-- JavaScriptは変更なし --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportModal = document.getElementById('reportModal');
        const reasonSelect = document.getElementById('reason_id');
        const subReasonContainer = document.getElementById('sub_reason_container');
        const subReasonSelect = document.getElementById('sub_reason_id');
        const submitReportButton = document.getElementById('submitReportButton');
        const loadingSubReasons = document.getElementById('loadingSubReasons');
        const reportForm = document.getElementById('reportForm'); // フォーム要素を取得

        // 初期状態では送信ボタンと詳細理由コンテナを非表示
        submitReportButton.style.display = 'none';
        subReasonContainer.style.display = 'none';

        // 通報理由（大まかな選択）が変更されたときの処理
        reasonSelect.addEventListener('change', function () {
            const selectedReasonId = this.value;

            // 子理由をクリア
            subReasonSelect.innerHTML = '<option value="">選択してください</option>';
            // 詳細理由コンテナを非表示に戻し、送信ボタンも非表示にする
            subReasonContainer.style.display = 'none';
            submitReportButton.style.display = 'none';
            subReasonSelect.removeAttribute('required');

            if (selectedReasonId) {
                loadingSubReasons.style.display = 'block'; // ローディング表示
                fetch(`/api/report-reasons/${selectedReasonId}/children`)
                    .then(response => response.json())
                    .then(data => {
                        loadingSubReasons.style.display = 'none'; // ローディング非表示

                        if (data.length > 0) {
                            const fragment = document.createDocumentFragment();
                            data.forEach(subReason => {
                                const option = document.createElement('option');
                                option.value = subReason.id;
                                option.textContent = subReason.reason_text;
                                fragment.appendChild(option);
                            });
                            subReasonSelect.appendChild(fragment);
                            subReasonContainer.style.display = 'block'; // 子理由のセレクトボックスを表示
                            subReasonSelect.setAttribute('required', 'required');
                        } else {
                            subReasonContainer.style.display = 'none';
                            subReasonSelect.removeAttribute('required');
                            // 子理由がない場合は、大まかな理由が選択されていれば即座に送信ボタンを表示
                            submitReportButton.style.display = 'inline-block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching sub reasons:', error);
                        alert('詳細な理由の取得に失敗しました。');
                        loadingSubReasons.style.display = 'none';
                        subReasonContainer.style.display = 'none';
                        subReasonSelect.removeAttribute('required');
                        submitReportButton.style.display = 'none';
                    });
            }
        });

        // 詳細な理由が選択されたときの処理
        subReasonSelect.addEventListener('change', function() {
            if (this.value) { // 何らかのオプションが選択された場合
                submitReportButton.style.display = 'inline-block'; // 送信ボタンを表示
            } else {
                const selectedReasonId = reasonSelect.value;
                if (selectedReasonId && subReasonSelect.options.length <= 1) { // 選択肢が「選択してください」のみの場合
                    submitReportButton.style.display = 'inline-block';
                } else {
                    submitReportButton.style.display = 'none';
                }
            }
        });

        // モーダルが表示される直前に、data-属性から値を取得してフォームにセット
        reportModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const reportableType = button.getAttribute('data-reportable-type');
            const reportableId = button.getAttribute('data-reportable-id');
            const reportedUserId = button.getAttribute('data-reported-user-id');

            reportModal.querySelector('#reportable_type').value = reportableType;
            reportModal.querySelector('#reportable_id').value = reportableId;
            reportModal.querySelector('#reported_user_id').value = reportedUserId;

            // モーダルが開くときにフォームをリセットし、初期状態に戻す
            reportForm.reset();
            reasonSelect.value = ''; // 明示的にリセット
            subReasonSelect.innerHTML = '<option value="">選択してください</option>'; // 明示的にリセット
            subReasonContainer.style.display = 'none';
            subReasonSelect.removeAttribute('required');
            submitReportButton.style.display = 'none';
            loadingSubReasons.style.display = 'none';
        });

        // モーダルが完全に閉じられたときにフォームをリセット
        reportModal.addEventListener('hidden.bs.modal', function () {
            reportForm.reset();
            // JavaScriptで制御している表示状態もリセット
            subReasonContainer.style.display = 'none';
            subReasonSelect.innerHTML = '<option value="">選択してください</option>';
            subReasonSelect.removeAttribute('required');
            submitReportButton.style.display = 'none';
            loadingSubReasons.style.display = 'none';
        });
    });
</script>
@endpush