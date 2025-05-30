@extends('layouts.app')

@section('title', 'スキル管理')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">スキル管理</h1>

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
        <div class="card-header bg-success text-white">
            スキル一覧
        </div>
        <div class="card-body">
            {{-- 検索フォーム --}}
            <form action="{{ route('admin.skills.index') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="タイトルまたは説明で検索" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">検索</button>
                    @if(request('search'))
                        <a href="{{ route('admin.skills.index') }}" class="btn btn-outline-danger">クリア</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>タイトル</th>
                            <th>説明</th>
                            <th>所有者</th>
                            <th>登録日</th>
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($skills as $skill)
                            <tr>
                                <td>{{ $skill->id }}</td>
                                <td>{{ $skill->title }}</td>
                                <td>{{ Str::limit($skill->description, 50) }}</td> {{-- 説明を短く表示 --}}
                                <td>{{ $skill->user->name ?? '不明' }}</td> {{-- リレーションがあれば表示 --}}
                                <td>{{ $skill->created_at ? $skill->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.skills.edit', $skill) }}" class="btn btn-sm btn-info text-white me-1">編集</a>
                                    <form action="{{ route('admin.skills.destroy', $skill) }}" method="POST" class="d-inline-block" onsubmit="return confirm('本当にこのスキルを削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">スキルが見つかりません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーションリンク --}}
            <div class="d-flex justify-content-center">
                {{ $skills->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection