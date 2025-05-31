@extends('layouts.admin')

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
            <form action="{{ route('admin.reports.index') }}" method="GET" class="mb-3">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="status" class="col-form-label">ステータス:</label>
                    </div>
                    <div class="col-md-3">
                        <select name="status" id="status" class="form-select">
                            <option value="unprocessed" {{ request('status') === 'unprocessed' ? 'selected' : '' }}>未処理</option>
                            <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>処理済み</option>
                            <option value="ignored" {{ request('status') === 'ignored' ? 'selected' : '' }}>無視</option>
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>全て</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="通報内容、ユーザー名で検索" value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary" type="submit">フィルター/検索</button>
                        @if(request('status') !== 'unprocessed' || request('search'))
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-danger">クリア</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>通報者</th>
                            <th>報告対象者</th>
                            <th>大まかな理由</th>
                            <th>詳細な理由</th>
                            <th>コメント</th>
                            <th>ステータス</th>
                            <th>通報日時</th>
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
                                <td><span class="badge {{ $report->status === 'unprocessed' ? 'bg-danger' : ($report->status === 'processed' ? 'bg-success' : 'bg-secondary') }}">{{
                                    match($report->status) {
                                        'unprocessed' => '未処理',
                                        'processed' => '処理済み',
                                        'ignored' => '無視',
                                        default => $report->status,
                                    }
                                }}</span></td>
                                <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    {{-- アクションボタンのグループ --}}
                                    <div class="btn-group" role="group" aria-label="Report Actions">
                                        {{-- ユーザーBANボタン (通報対象がユーザーの場合のみ) --}}
                                       @if ($report->reportedUser) {{-- ★ここを変更: reportedUserの存在チェックに変更 --}}
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

                                        {{-- 通報ステータス更新ボタン（ドロップダウンなどでも良い） --}}
                                        <button type="button" class="btn btn-sm btn-info text-white mark-reviewed-btn"
                                                data-report-id="{{ $report->id }}"
                                                data-current-status="{{ $report->status }}"
                                                onclick="updateReportStatus(this)">
                                            確認済みにする
                                        </button>
                                    </div>
                                    {{-- ここに通報対象コンテンツの削除ボタンなど（任意） --}}
                                    {{-- 例: スキル削除ボタン (通報対象がスキルの場合のみ)
                                    @if ($report->reportable_type === 'App\Models\Skill' && $report->reportable)
                                        <form action="{{ route('admin.skills.destroy', $report->reportable) }}" method="POST" class="d-inline-block" onsubmit="return confirm('本当にこのスキルを削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">スキル削除</button>
                                        </form>
                                    @endif --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">通報が見つかりません。</td>
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
                @method('PUT') {{-- または PATCH --}}
                <div class="modal-body">
                    <p>ユーザー <strong id="banUserName"></strong> のBAN状態を変更します。</p>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="isBannedSwitch" name="is_banned">
                        <label class="form-check-label" for="isBannedSwitch">このユーザーをBANする</label>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="banReason" class="form-label">BAN理由 (任意)</label>
                        <textarea class="form-control" id="banReason" name="ban_reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger">適用</button>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ユーザーBANモーダル処理 (変更なし)
    const banUserModal = document.getElementById('banUserModal');
    const banUserForm = document.getElementById('banUserForm'); 

    banUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const reportId = button.getAttribute('data-report-id');
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');
        const isBanned = button.getAttribute('data-is-banned') === '1';

        const userNameElem = banUserModal.querySelector('#banUserName');
        const isBannedSwitch = banUserModal.querySelector('#isBannedSwitch');

        userNameElem.textContent = userName;
        isBannedSwitch.checked = isBanned;
        
        banUserForm.action = `/admin/users/${userId}/ban`;
        
        let hiddenReportIdInput = banUserForm.querySelector('input[name="report_id"]');
        if (!hiddenReportIdInput) {
            hiddenReportIdInput = document.createElement('input');
            hiddenReportIdInput.type = 'hidden';
            hiddenReportIdInput.name = 'report_id';
            banUserForm.appendChild(hiddenReportIdInput);
        }
        hiddenReportIdInput.value = reportId;
    });

    // ユーザー警告モーダル処理 (ここからwarnUserFormのsubmitイベントリスナーを削除)
    const warnUserModal = document.getElementById('warnUserModal');
    // warnUserFormの取得はそのまま残す
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

    // ★ここにあった warnUserForm.addEventListener('submit', ...) ブロックを削除する！★
    // このブロックを削除することで、ブラウザのデフォルトのフォーム送信挙動（ページ遷移）が有効になります。


    // 通報ステータス更新関数 (ここはAJAXのままにするか、同様に通常フォーム送信に変更するか)
    // ここは現状AJAXのままにしておきます。もしこちらもリロードにしたい場合は同様に修正が必要です。
    function updateReportStatus(button) {
        const reportId = button.getAttribute('data-report-id');
        const currentStatus = button.getAttribute('data-current-status');
        let newStatus = 'processed';

        if (currentStatus === 'unprocessed') {
            newStatus = 'processed';
            if (!confirm(`この通報を「処理済み」に更新しますか？`)) {
                return;
            }
        } else if (currentStatus === 'processed') {
            newStatus = 'ignored';
            if (!confirm(`この通報を「無視」に更新しますか？`)) {
                return;
            }
        } else if (currentStatus === 'ignored') {
            newStatus = 'unprocessed';
            if (!confirm(`この通報を「未処理」に戻しますか？`)) {
                return;
            }
        } else {
            if (!confirm(`通報ID ${reportId} のステータスを「処理済み」に更新しますか？`)) {
                 return;
            }
            newStatus = 'processed';
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
                // SweetAlert2は残しておきますが、alertでもOKです
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
                Swal.fire({
                    icon: 'error',
                    title: 'エラー',
                    text: data.message || 'ステータス更新に失敗しました。',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'エラー',
                text: 'エラーが発生しました: ' + error.message,
                confirmButtonText: 'OK'
            });
        });
    }
    window.updateReportStatus = updateReportStatus;
});
</script>
@endpush