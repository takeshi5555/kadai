@extends('layouts.moderator') 

@section('title', '通報管理')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">通報管理</h1>

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
        <div class="card-header bg-warning text-white">
            通報一覧
        </div>
        <div class="card-body">
            {{-- フィルター/検索フォーム --}}
            {{-- ★変更点1: フォームのアクションURLをmoderatorルートに変更 --}}
            <form action="{{ route('moderator.reports.index') }}" method="GET" class="mb-3">
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
                        <button class="btn btn-outline-secondary" type="submit">フィルター/検索</button>
                        @if(request('status') !== 'unprocessed' || request('search'))
                            {{-- ★変更点2: クリアボタンのリンクをmoderatorルートに変更 --}}
                            <a href="{{ route('moderator.reports.index') }}" class="btn btn-outline-danger">クリア</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            {{-- ID列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    ID
                                    {{-- ★変更点3: ソートリンクをmoderatorルートに変更 --}}
                                    <a href="{{ route('moderator.reports.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => (request('sort') == 'id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'id')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 通報者列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    通報者
                                    {{-- ★変更点4: ソートリンクをmoderatorルートに変更 --}}
                                    <a href="{{ route('moderator.reports.index', array_merge(request()->query(), ['sort' => 'reporting_user_id', 'direction' => (request('sort') == 'reporting_user_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'reporting_user_id')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 報告対象者列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    報告対象者
                                    {{-- ★変更点5: ソートリンクをmoderatorルートに変更 --}}
                                    <a href="{{ route('moderator.reports.index', array_merge(request()->query(), ['sort' => 'reported_user_id', 'direction' => (request('sort') == 'reported_user_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'reported_user_id')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 大まかな理由列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    大まかな理由
                                    {{-- ★変更点6: ソートリンクをmoderatorルートに変更 --}}
                                    <a href="{{ route('moderator.reports.index', array_merge(request()->query(), ['sort' => 'reason_id', 'direction' => (request('sort') == 'reason_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'reason_id')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
                                        @endif
                                    </a>
                                </div>
                            </th>
                            {{-- 詳細な理由列のソートボタン --}}
                            <th>
                                <div class="d-flex align-items-center">
                                    詳細な理由
                                    {{-- ★変更点7: ソートリンクをmoderatorルートに変更 --}}
                                    <a href="{{ route('moderator.reports.index', array_merge(request()->query(), ['sort' => 'sub_reason_id', 'direction' => (request('sort') == 'sub_reason_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'sub_reason_id')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
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
                                    {{-- ★変更点8: ソートリンクをmoderatorルートに変更 --}}
                                    <a href="{{ route('moderator.reports.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => (request('sort') == 'created_at' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="ms-1 text-decoration-none text-dark">
                                        @if(request('sort') == 'created_at')
                                            @if(request('direction') == 'asc')
                                                &#9650;
                                            @else
                                                &#9660;
                                            @endif
                                        @else
                                            &#9660;
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
                                <td>{{ $report->reportedUser->name ?? '不明なユーザー' }}</td>

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
                                    <div class="btn-group" role="group" aria-label="Report Actions">
                                        {{-- ユーザーBANボタン (通報対象がユーザーの場合のみ) --}}
                                        @if ($report->reportedUser)
                                            <button type="button" class="btn btn-sm btn-danger ban-user-btn me-1"
                                                    data-bs-toggle="modal" data-bs-target="#banUserModal"
                                                    data-report-id="{{ $report->id }}"
                                                    data-user-id="{{ $report->reportedUser->id }}"
                                                    data-user-name="{{ $report->reportedUser->name }}"
                                                    data-is-banned="{{ $report->reportedUser->is_banned ? '1' : '0' }}">
                                                {{ $report->reportedUser->is_banned ? 'BAN解除' : 'ユーザーBAN' }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning text-dark me-1 warn-user-btn"
                                                    data-bs-toggle="modal" data-bs-target="#warnUserModal"
                                                    data-report-id="{{ $report->id }}"
                                                    data-user-id="{{ $report->reportedUser->id }}"
                                                    data-user-name="{{ $report->reportedUser->name }}">
                                                警告
                                            </button>
                                        @endif

                                        {{-- 通報ステータス更新ボタン --}}
                                        @if($report->status === 'unprocessed')
                                            <button type="button" class="btn btn-sm btn-info text-white mark-reviewed-btn"
                                                    data-report-id="{{ $report->id }}"
                                                    data-current-status="{{ $report->status }}"
                                                    onclick="updateReportStatus(this)">
                                                処理済みにする
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary mark-reviewed-btn"
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
                                <td colspan="9" class="text-center">通報が見つかりません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーションリンク --}}
            <div class="d-flex justify-content-center">
                {{ $reports->appends(request()->query())->links('pagination::simple-bootstrap-4') }}

            </div>
        </div>
    </div>
</div>


{{-- ユーザーBANモーダル --}}
<div class="modal fade" id="banUserModal" tabindex="-1" aria-labelledby="banUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="banUserModalLabel">ユーザーBAN/BAN解除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="banUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p id="banActionDescription">ユーザー <strong id="banUserName"></strong> のBAN状態を変更します。</p>
                    <input type="hidden" id="isBannedHidden" name="is_banned" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger" id="banSubmitButton">適用</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ユーザー警告モーダル --}}
<div class="modal fade" id="warnUserModal" tabindex="-1" aria-labelledby="warnUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warnUserModalLabel">ユーザーに警告/注意</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="warnUserForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>ユーザー <strong id="warnUserName"></strong> に警告メッセージを送信します。</p>
                    <div class="mb-3">
                        <label for="warningMessage" class="form-label">警告メッセージ</label>
                        <textarea class="form-control" id="warningMessage" name="message_content" rows="5" required></textarea>
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

@push('scripts')
{{-- JavaScript部分の修正箇所のみ表示 --}}
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
        
        // ★修正: モデレーター用ルートに変更★
        banUserForm.action = `/moderator/users/${userId}/ban`;
        
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
        
        // ★修正: モデレーター用ルートに変更★
        warnUserForm.action = `/moderator/reports/${reportId}/warn`; 
        
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

        // ★修正: モデレーター用ルートに変更★
        fetch(`/moderator/reports/${reportId}`, {
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