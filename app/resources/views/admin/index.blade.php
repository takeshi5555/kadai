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
                    <h5 class="card-title text-success">登録スキル数</h5>
                    <p class="card-text h2">{{ $skillCount }}</p>
                    <a href="{{ route('admin.skills.index') }}" class="btn btn-success mt-2">スキル管理へ</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-warning">未処理通報数</h5>
                    <p class="card-text h2">{{ $unprocessedReportCount }}</p>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-warning text-white mt-2">通報管理へ</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection