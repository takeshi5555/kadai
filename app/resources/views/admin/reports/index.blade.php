@extends('layouts.admin')

@section('title', '通報管理')

@section('content')
<div class="container my-4">
    <h1 class="mb-4 text-muted">通報管理</h1> {{-- H1の文字色をグレーに --}}

    {{-- フラッシュメッセージ --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white"> {{-- カードヘッダーをグレーに --}}
            通報一覧
        </div>
        <div class="card-body">
            {{-- フィルター/検索フォーム --}}
            <form action="{{ route('admin.reports.index') }}" method="GET" class="mb-4 p-3 border rounded bg-light"> {{-- フォームのスタイルを調整 --}}
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="status" class="col-form-label">ステータス:</label>
                    </div>
                    <div class="col-md-3">
                        <select name="status" id="status" class="form-select">
                            <option value="unprocessed" {{ request('status', 'unprocessed') === 'unprocessed' ? 'selected' : '' }}>未処理</option>
                            <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>処理済み</option>
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>全て</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="通報内容、ユーザー名で検索" value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">フィルター/検索</button> {{-- ボタンカラーをプライマリに --}}
                        @if(request('status') !== 'unprocessed' || request('search'))
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary ms-2">クリア</a> {{-- クリアボタンのスタイル変更 --}}
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light"> {{-- ヘッダーの背景色を明るく --}}
                        <tr>
                            {{-- ID列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    ID
                                    <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => (request('sort') == 'id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'id')
                                            @if(request('direction') == 'asc')
                                                <i class="bi bi-caret-up-fill"></i> {{-- Bootstrap Iconsを使用 --}}
                                            @else
                                                <i class="bi bi-caret-down-fill"></i> {{-- Bootstrap Iconsを使用 --}}
                                            @endif
                                        @else
                                            <i class="bi bi-caret-down"></i> {{-- Bootstrap Iconsを使用 --}}
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 通報者列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    通報者
                                    <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['sort' => 'reporting_user_id', 'direction' => (request('sort') == 'reporting_user_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'reporting_user_id')
                                            @if(request('direction') == 'asc')
                                                <i class="bi bi-caret-up-fill"></i>
                                            @else
                                                <i class="bi bi-caret-down-fill"></i>
                                            @endif
                                        @else
                                            <i class="bi bi-caret-down"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 報告対象者列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    報告対象者
                                    <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['sort' => 'reported_user_id', 'direction' => (request('sort') == 'reported_user_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'reported_user_id')
                                            @if(request('direction') == 'asc')
                                                <i class="bi bi-caret-up-fill"></i>
                                            @else
                                                <i class="bi bi-caret-down-fill"></i>
                                            @endif
                                        @else
                                            <i class="bi bi-caret-down"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 大まかな理由列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    大まかな理由
                                    <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['sort' => 'reason_id', 'direction' => (request('sort') == 'reason_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'reason_id')
                                            @if(request('direction') == 'asc')
                                                <i class="bi bi-caret-up-fill"></i>
                                            @else
                                                <i class="bi bi-caret-down-fill"></i>
                                            @endif
                                        @else
                                            <i class="bi bi-caret-down"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 詳細な理由列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    詳細な理由
                                    <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['sort' => 'sub_reason_id', 'direction' => (request('sort') == 'sub_reason_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'sub_reason_id')
                                            @if(request('direction') == 'asc')
                                                <i class="bi bi-caret-up-fill"></i>
                                            @else
                                                <i class="bi bi-caret-down-fill"></i>
                                            @endif
                                        @else
                                            <i class="bi bi-caret-down"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th>コメント</th>
                            <th>ステータス</th>
                            {{-- 通報日時列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    通報日時
                                    <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => (request('sort') == 'created_at' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'created_at')
                                            @if(request('direction') == 'asc')
                                                <i class="bi bi-caret-up-fill"></i>
                                            @else
                                                <i class="bi bi-caret-down-fill"></i>
                                            @endif
                                        @else
                                            <i class="bi bi-caret-down"></i>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>{{ $report->reportingUser->name ?? '匿名ユーザー' }}</td>
                                <td>
                                    @if ($report->reportedUser)
                                        <a href="#" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="ユーザーID: {{ $report->reportedUser->id }}">
                                            {{ $report->reportedUser->name }}
                                        </a>
                                    @else
                                        不明なユーザー
                                    @endif
                                </td>
                                <td>{{ $report->reason->reason_text ?? '不明' }}</td>
                                <td>{{ $report->subReason->reason_text ?? 'なし' }}</td>
                                <td>{{ Str::limit($report->comment, 30) }}</td>
                                <td><span class="badge {{ $report->status === 'unprocessed' ? 'bg-danger' : 'bg-success' }}">{{
                                    match($report->status) {
                                        'unprocessed' => '未処理',
                                        'processed' => '処理済み',
                                        default => $report->status,
                                    }
                                }}</span></td>
                                <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    {{-- アクションボタンのグループ --}}
                                    <div class="d-flex flex-wrap gap-1"> {{-- ボタン間に少し隙間 --}}
                                        {{-- ユーザーBANボタン (通報対象がユーザーの場合のみ) --}}
                                        @if ($report->reportedUser)
                                            <button type="button" class="btn btn-sm {{ $report->reportedUser->is_banned ? 'btn-outline-secondary' : 'btn-danger' }} ban-user-btn"
                                                    data-bs-toggle="modal" data-bs-target="#banUserModal"
                                                    data-report-id="{{ $report->id }}"
                                                    data-user-id="{{ $report->reportedUser->id }}"
                                                    data-user-name="{{ $report->reportedUser->name }}"
                                                    data-is-banned="{{ $report->reportedUser->is_banned ? '1' : '0' }}">
                                                {{ $report->reportedUser->is_banned ? 'BAN解除' : 'ユーザーBAN' }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning warn-user-btn"
                                                    data-bs-toggle="modal" data-bs-target="#warnUserModal"
                                                    data-report-id="{{ $report->id }}"
                                                    data-user-id="{{ $report->reportedUser->id }}"
                                                    data-user-name="{{ $report->reportedUser->name }}">
                                                警告
                                            </button>
                                        @endif

                                        {{-- 通報ステータス更新ボタン --}}
                                        @if($report->status === 'unprocessed')
                                            <button type="button" class="btn btn-sm btn-success text-white mark-reviewed-btn"
                                                    data-report-id="{{ $report->id }}"
                                                    data-current-status="{{ $report->status }}"
                                                    onclick="updateReportStatus(this)">
                                                処理済みにする
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary mark-reviewed-btn"
                                                    data-report-id="{{ $report->id }}"
                                                    data-current-status="{{ $report->status }}"
                                                    onclick="updateReportStatus(this)">
                                                未処理に戻す
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">通報が見つかりません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーションリンク --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $reports->appends(request()->query())->links('pagination::bootstrap-4') }} {{-- Bootstrap 5のスタイルを適用 --}}
            </div>
        </div>
    </div>
</div>

{{-- ユーザーBANモーダル --}}
<div class="modal fade" id="banUserModal" tabindex="-1" aria-labelledby="banUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white"> {{-- モーダルヘッダーもグレーに --}}
                <h5 class="modal-title" id="banUserModalLabel">ユーザーBAN/BAN解除</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> {{-- クローズボタンの色を白に --}}
            </div>
            <form id="banUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p id="banActionDescription" class="mb-3">ユーザー <strong id="banUserName" class="text-primary"></strong> のBAN状態を変更します。</p>
                    <input type="hidden" id="isBannedHidden" name="is_banned" value="">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="banConfirmCheckbox">
                        <label class="form-check-label" for="banConfirmCheckbox">
                            この操作を理解し、実行します。
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger" id="banSubmitButton" disabled>適用</button> {{-- チェックボックスがチェックされるまで無効 --}}
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ユーザー警告モーダル --}}
<div class="modal fade" id="warnUserModal" tabindex="-1" aria-labelledby="warnUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white"> {{-- モーダルヘッダーもグレーに --}}
                <h5 class="modal-title" id="warnUserModalLabel">ユーザーに警告/注意</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="warnUserForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">ユーザー <strong id="warnUserName" class="text-primary"></strong> に警告メッセージを送信します。</p>
                    <div class="mb-3">
                        <label for="warningMessage" class="form-label">警告メッセージ</label>
                        <textarea class="form-control" id="warningMessage" name="message_content" rows="5" required placeholder="警告メッセージを入力してください"></textarea>
                        <div class="form-text">このメッセージはユーザーに直接送信されます。</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-warning text-dark">警告を送信</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root {
        --skillswap-primary:rgb(110, 161, 209); /* 新しいメインブルー（少し落ち着いた青） */
        --skillswap-primary-dark:rgb(121, 165, 203); /* メインカラーより濃い青（ホバー用やアクセントに） */
        --skillswap-text-light: #ffffff; /* 明るい背景用テキスト（白） */
        --skillswap-text-dark: #333333; /* 暗い背景用テキスト（濃いグレー） */
        --status-success-light: #C5E1F7; /* 薄い青 */
        --status-info: #6c757d; /* ミディアムグレー */
        --status-info-dark: #5a6268; /* ミディアムグレーより濃い */
        /* 追加・修正：警告と処理済みの色をより明確に */
        --status-warning-custom: #FFC107; /* 警告色（Bootstrapのwarningに近い黄色オレンジ） */
        --status-warning-custom-dark: #e0a800; /* 警告色の濃いバージョン */
        /* 処理済み色をグレー系に再定義 */
        --status-processed-custom: #6c757d; /* ミディアムグレー（status-infoと同じ） */
        --status-processed-custom-dark: #5a6268; /* 濃いグレー（status-info-darkと同じ） */
    }

    /* テーブルヘッダーのテキスト色を調整 */
    .table thead th {
        color: var(--skillswap-text-dark); /* ダークカラーのテキストに */
    }
    /* ソートアイコンの色を調整 */
    .table thead th a {
        color: var(--skillswap-primary) !important; /* メインカラーに */
    }
    .table thead th a:hover {
        color: var(--skillswap-primary-dark) !important; /* ホバーで少し濃く */
    }
    /* 編集ボタンのスタイルを調整 (btn-infoを上書き) */
    .btn-info {
        background-color: var(--status-info) !important; /* グレー系の色に修正 */
        border-color: var(--status-info) !important;
        color: var(--skillswap-text-light) !important; /* 白文字 */
    }
    .btn-info:hover {
        background-color: var(--status-info-dark) !important; /* ホバーで少し濃いグレーに */
        border-color: var(--status-info-dark) !important;
    }
    /* モーダルのヘッダーにメインカラーを適用 */
    #editSkillModal .modal-header {
        background-color: var(--skillswap-primary-dark);
        color: var(--skillswap-text-light);
    }
    /* モーダルのタイトル色を調整 */
    #editSkillModal .modal-title {
        color: var(--skillswap-text-light);
    }

    /* --- フォームとボタンのスタイルの修正 --- */

    /* カードヘッダーを元々定義されているbg-secondaryに合わせておく */
    .card-header.bg-secondary {
        background-color: var(--status-info) !important; /* ミディアムグレー */
        color: var(--skillswap-text-light) !important;
    }

    /* 検索/フィルターボタンの変更 */
    /* Bladeファイルでは `btn-primary` が使われているので、これをターゲットに */
    .btn-primary {
        background-color: var(--status-success-light) !important; /* 薄い青 */
        border-color: var(--skillswap-primary) !important; /* メインの青で枠線 */
        color: var(--skillswap-text-dark) !important; /* 文字色を濃い色に（背景が薄いため）*/
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .btn-primary:hover {
        background-color: var(--skillswap-primary) !important; /* ホバー時はメインの青 */
        border-color: var(--skillswap-primary-dark) !important;
        color: var(--skillswap-text-light) !important; /* ホバー時のテキストは白に */
    }

    /* 警告ボタンの色変更 */
    /* Bladeファイルでは `btn-warning` が使われているので、これをターゲットに */
    .btn-warning {
        background-color: var(--status-warning-custom) !important; /* 黄色オレンジ */
        border-color: var(--status-warning-custom-dark) !important;
        color: var(--skillswap-text-dark) !important; /* 文字色は濃い色に */
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .btn-warning:hover {
        background-color: var(--status-warning-custom-dark) !important;
        border-color: var(--status-warning-custom-dark) !important;
        color: var(--skillswap-text-dark) !important; /* ホバー時も濃い色 */
    }

    /* 処理済みにするボタンの色変更 */
    /* Bladeファイルでは `btn-success` が使われているので、これをターゲットに */
    .btn-success {
        background-color: var(--status-processed-custom) !important; /* グレー */
        border-color: var(--status-processed-custom-dark) !important;
        color: var(--skillswap-text-light) !important; /* 白文字 */
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .btn-success:hover {
        background-color: var(--status-processed-custom-dark) !important;
        border-color: var(--status-processed-custom-dark) !important;
        color: var(--skillswap-text-light) !important;
    }

    /* ステータスバッジの色の変更 */
    /* 未処理 (`bg-danger`) と処理済み (`bg-success`) の色を上書き */
    .badge.bg-danger {
        background-color: var(--status-warning-custom) !important; /* 未処理を警告色に */
        color: var(--skillswap-text-dark) !important;
    }

    .badge.bg-success {
        background-color: var(--status-processed-custom) !important; /* 処理済みをグレーに */
        color: var(--skillswap-text-light) !important;
    }

    /* モーダルのヘッダーとクローズボタン */
    /* Bladeファイルでは `bg-secondary` が使われているので、これをターゲットに */
    .modal-header.bg-secondary {
        background-color: var(--skillswap-primary-dark) !important; /* モーダルヘッダーもメインの濃い青に */
        color: var(--skillswap-text-light) !important;
    }
    .modal-header .btn-close-white {
        filter: brightness(0) invert(1); /* Bootstrapのbtn-close-whiteが効かない場合のために明示的に白くする */
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ユーザーBANモーダル処理
    const banUserModal = document.getElementById('banUserModal');
    const banUserForm = document.getElementById('banUserForm'); 

    banUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const reportId = button.getAttribute('data-report-id');
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');
        const isBanned = button.getAttribute('data-is-banned') === '1';

        // モーダル内の要素を取得
        const userNameElem = banUserModal.querySelector('#banUserName');
        const isBannedHidden = banUserModal.querySelector('#isBannedHidden');
        const banActionDescription = banUserModal.querySelector('#banActionDescription');
        const banSubmitButton = banUserModal.querySelector('#banSubmitButton');

        // ユーザー名を設定
        userNameElem.textContent = userName;
        
        // 現在のBAN状態に応じてモーダルの内容を変更
        if (isBanned) {
            // 現在BANされている場合 - BAN解除の設定
            isBannedHidden.value = '0'; // BAN解除するために0に設定
            banActionDescription.innerHTML = `ユーザー <strong>${userName}</strong> は現在BANされています。BAN解除しますか？`;
            banSubmitButton.textContent = 'BAN解除';
            banSubmitButton.className = 'btn btn-success';
        } else {
            // 現在BANされていない場合 - BANの設定
            isBannedHidden.value = '1'; // BANするために1に設定
            banActionDescription.innerHTML = `ユーザー <strong>${userName}</strong> をBANしますか？`;
            banSubmitButton.textContent = 'ユーザーBAN';
            banSubmitButton.className = 'btn btn-danger';
        }
        
        // フォームのactionを設定
        banUserForm.action = `/admin/users/${userId}/ban`;
        
        // 隠しフィールドでreport_idを送信
        let hiddenReportIdInput = banUserForm.querySelector('input[name="report_id"]');
        if (!hiddenReportIdInput) {
            hiddenReportIdInput = document.createElement('input');
            hiddenReportIdInput.type = 'hidden';
            hiddenReportIdInput.name = 'report_id';
            banUserForm.appendChild(hiddenReportIdInput);
        }
        hiddenReportIdInput.value = reportId;
        
        // BAN理由フィールドをクリア（フィールドが存在する場合）
        const banReasonField = banUserModal.querySelector('#banReason');
        if (banReasonField) {
            banReasonField.value = '';
        }
    });

    // ユーザー警告モーダル処理
    const warnUserModal = document.getElementById('warnUserModal');
    const warnUserForm = document.getElementById('warnUserForm'); 

    warnUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const reportId = button.getAttribute('data-report-id');
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');

        const userNameElem = warnUserModal.querySelector('#warnUserName');

        userNameElem.textContent = userName;
        
        // モーダルが開かれるときにフォームのactionを設定する
        warnUserForm.action = `/admin/reports/${reportId}/warn`; 
        
        let hiddenUserIdInput = warnUserForm.querySelector('input[name="user_id"]');
        if (!hiddenUserIdInput) {
            hiddenUserIdInput = document.createElement('input');
            hiddenUserIdInput.type = 'hidden';
            hiddenUserIdInput.name = 'user_id';
            warnUserForm.appendChild(hiddenUserIdInput);
        }
        hiddenUserIdInput.value = userId;

        let hiddenReportIdInput = warnUserForm.querySelector('input[name="report_id"]');
        if (!hiddenReportIdInput) {
            hiddenReportIdInput = document.createElement('input');
            hiddenReportIdInput.type = 'hidden';
            hiddenReportIdInput.name = 'report_id';
            warnUserForm.appendChild(hiddenReportIdInput);
        }
        hiddenReportIdInput.value = reportId;

        warnUserForm.querySelector('#warningMessage').value = '';
    });

    // 通報ステータス更新関数
    function updateReportStatus(button) {
        const reportId = button.getAttribute('data-report-id');
        const currentStatus = button.getAttribute('data-current-status');
        let newStatus = 'processed';
        let confirmMessage = '';

        if (currentStatus === 'unprocessed') {
            newStatus = 'processed';
            confirmMessage = 'この通報を「処理済み」に更新しますか？';
        } else if (currentStatus === 'processed') {
            newStatus = 'unprocessed';
            confirmMessage = 'この通報を「未処理」に戻しますか？';
        } else {
            newStatus = 'processed';
            confirmMessage = `通報ID ${reportId} のステータスを「処理済み」に更新しますか？`;
        }

        if (!confirm(confirmMessage)) {
            return;
        }

        fetch(`/admin/reports/${reportId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'ステータス更新に失敗しました。');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // SweetAlert2がある場合は使用、なければalertを使用
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '成功',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    alert(data.message);
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'エラー',
                        text: data.message || 'ステータス更新に失敗しました。',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(data.message || 'ステータス更新に失敗しました。');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'エラー',
                    text: 'エラーが発生しました: ' + error.message,
                    confirmButtonText: 'OK'
                });
            } else {
                alert('エラーが発生しました: ' + error.message);
            }
        });
    }
    window.updateReportStatus = updateReportStatus;
});
</script>
@endpush