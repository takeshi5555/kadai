@extends('layouts.admin')

@section('title', '管理者ダッシュボード')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">管理者ダッシュボード</h1>

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

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">登録ユーザー数</h5>
                    <p class="card-text h2">{{ $userCount }}</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary mt-2">ユーザー管理へ</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--skillswap-primary-dark);">登録スキル数</h5>
                    <p class="card-text h2">{{ $skillCount }}</p>
                    <a href="{{ route('admin.skills.index') }}" class="btn btn-skill-info mt-2">スキル管理へ</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-secondary">未処理通報数</h5>
                    <p class="card-text h2">{{ $unprocessedReportCount }}</p>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary mt-2">通報管理へ</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* --- CSS変数の定義（必須） --- */
:root {
    --skillswap-primary:rgb(110, 161, 209); /* 新しいメインブルー（少し落ち着いた青） */
    --skillswap-primary-dark:rgb(121, 165, 203); /* メインカラーより濃い青（ホバー用やアクセントに） */
    --skillswap-text-light: #ffffff; /* 明るい背景用テキスト（白） */
    --skillswap-text-dark: #333333; /* 暗い背景用テキスト（濃いグレー） */
    --status-success-light: #C5E1F7; /* 薄い青 */
    --status-info: #6c757d; /* ミディアムグレー */
    --status-info-dark: #5a6268;
}

/* --- カードの高さ調整（任意） --- */
.col-md-4.mb-3 .card {
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.col-md-4.mb-3 .card-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* --- このページで使用するボタンのスタイル調整 --- */

/* メインのアクションボタン (btn-primary) */
.btn-primary {
    background-color: var(--skillswap-primary) !important;
    border-color: var(--skillswap-primary) !important;
    color: var(--skillswap-text-light) !important;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-primary:hover {
    background-color: var(--skillswap-primary-dark) !important;
    border-color: var(--skillswap-primary-dark) !important;
    color: var(--skillswap-text-light) !important;
}

/* btn-secondary の調整（落ち着いたグレー） */
.btn-secondary {
    background-color: var(--status-info) !important;
    border-color: var(--status-info) !important;
    color: var(--skillswap-text-light) !important;
}
.btn-secondary:hover {
    background-color: var(--status-info-dark) !important;
    border-color: var(--status-info-dark) !important;
}

/* スキル管理カードの「登録スキル数」用カスタムボタン (btn-skill-info) */
.btn-skill-info {
    background-color: var(--status-success-light) !important; /* 薄い青を背景に */
    border-color: var(--skillswap-primary) !important; /* メインの青で枠線 */
    color: var(--skillswap-text-light) !important; /* ★ここを変更★ 文字色を白に */
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

.btn-skill-info:hover {
    background-color: var(--skillswap-primary) !important; /* ホバー時はメインの青 */
    border-color: var(--skillswap-primary-dark) !important;
    color: var(--skillswap-text-light) !important; /* ホバー時のテキストも白に */
}

/* テキスト色の調整（直接クラスを使っている箇所向け） */
.text-primary {
    color: var(--skillswap-primary) !important;
}
.text-secondary {
    color: var(--status-info) !important;
}
</style>
@endpush