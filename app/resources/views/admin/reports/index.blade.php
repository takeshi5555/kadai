@extends('layouts.app')

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
                                    <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-info text-white">詳細</a>
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
                {{ $reports->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection