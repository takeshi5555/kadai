@extends('layouts.app') {{-- layouts/app.blade.php を継承 --}}

@section('title', 'マイページ') {{-- ページタイトルを設定 --}}

@section('content')
@php use Illuminate\Support\Str; @endphp {{-- Str::limit を使用するために追加 --}}

<div class="container" style="max-width: 960px; margin: 0 auto; padding: 20px;">
    <h1>{{ $user->name }}さんのマイページ</h1>

    <div class="user-info card" style="margin-bottom: 30px;">
        <div class="card-body">
            <h2>ユーザー情報</h2>
            <p><strong>ユーザー名:</strong> {{ $user->name }}</p>
            <p><strong>メールアドレス:</strong> {{ $user->email }}</p>
            <a href="{{ route('password.request') }}" class="btn btn-secondary mt-2">パスワードを再設定する</a>
        </div>
    </div>

    {{-- 未読メッセージがある場合に表示 --}}
    @if($unreadMessagesCount > 0)
    <div class="alert alert-info" style="
        background-color: #e0f7fa;
        border-color: #00bcd4;
        color: #006064;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    ">
        <strong>新着メッセージがあります！</strong>
        <p>{{ $unreadMessagesCount }}件の未読メッセージがあります。</p>
        <a href="{{ route('matching.history.index') }}" class="btn btn-primary" style="margin-top: 10px;">メッセージ履歴を見る</a>
    </div>
    @endif

    <div class="my-skills card" style="margin-bottom: 30px;">
        <div class="card-body">
            <h2>私が提供できるスキル</h2>
            @if($skills->isEmpty())
                <p>まだスキルを登録していません。</p>
                <a href="{{ route('skill.manage.index') }}" class="btn btn-success">新しいスキルを登録する</a>
            @else
                <div class="skill-grid">
                    @foreach($skills as $skill)
                    <div class="skill-card card">
                        <div class="card-body">
                            {{-- ★ここを title に変更 --}}
                            <h3>{{ $skill->title }} ({{ $skill->category }})</h3>
                            <p>{{ Str::limit($skill->description, 100) }}</p>
                            <p><strong>対応可能時間:</strong> {{ $skill->available_time }}</p>
                            <a href="{{ route('skill.detail.show', $skill->id) }}" class="btn btn-primary btn-sm">詳細を見る</a>
                            <a href="{{ route('skill.edit', $skill->id) }}" class="btn btn-secondary btn-sm">編集</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="{{ route('skill.manage.index') }}" class="btn btn-success">新しいスキルを登録・管理する</a>
                </div>
            @endif
        </div>
    </div>

    <div class="matching-history card">
        <div class="card-body">
            <h2>マッチング履歴</h2>
            @if($matchings->isEmpty())
                <p>まだマッチング履歴がありません。</p>
            @else
                <ul style="list-style: none; padding: 0;">
                    @foreach($matchings as $matching)
                        <li class="card" style="margin-bottom: 15px;">
                            <div class="card-body">
                                <h3>
                                    {{-- ★ここを title に変更 --}}
                                    <span style="color: #007bff;">{{ $matching->offeringSkill->title ?? 'N/A' }}</span>
                                    を提供する
                                    <span style="color: #28a745;">{{ $matching->offerUser->name ?? 'N/A' }}</span>
                                    さんと、
                                    {{-- ★ここを title に変更 --}}
                                    <span style="color: #007bff;">{{ $matching->receivingSkill->title ?? 'N/A' }}</span>
                                    をリクエストする
                                    <span style="color: #28a745;">{{ $matching->requestUser->name ?? 'N/A' }}</span>
                                    さんのマッチング
                                </h3>
                                <p><strong>ステータス:</strong> <span style="font-weight: bold; color: {{ $matching->status == 1 ? 'green' : ($matching->status == 3 ? 'red' : 'orange') }}">{{ $matching->statusText }}</span></p>
                                <p><strong>予定日時:</strong> {{ $matching->scheduled_at ? $matching->scheduled_at->format('Y-m-d H:i') : '未定' }}</p>
                                <p><strong>マッチングID:</strong> {{ $matching->id }}</p>

                                {{-- レビュー内容を直接表示するブロック --}}
                                @if($matching->status == 2) {{-- ステータスが「完了」の場合のみレビューを表示 --}}
                                    @if(Auth::id() == $matching->offerUser->id || Auth::id() == $matching->requestUser->id) {{-- 自分がマッチングに関わっている場合 --}}
                                        <div class="reviews-section mt-3 p-3 border rounded bg-light">
                                            {{-- 自分のレビューがある場合 --}}
                                            @if($matching->myReview)
                                                <h5 class="text-primary">あなたのレビュー</h5>
                                                {{-- ★ここを rating に変更 --}}
                                                <p><strong>評価:</strong> {{ $matching->myReview->rating }} / 5</p>
                                                <p><strong>コメント:</strong> {{ $matching->myReview->comment ?? 'コメントなし' }}</p>
                                            @else
                                                <p class="text-warning">あなたはまだレビューを投稿していません。</p>
                                                <a href="{{ route('review.form', ['matchingId' => $matching->id]) }}" class="btn btn-success btn-sm">相手をレビューする</a>
                                            @endif

                                            <hr class="my-2">

                                            {{-- 相手からのレビューがある場合 --}}
                                            @if($matching->reviewFromPartner)
                                                <h5 class="text-success">{{ $matching->reviewFromPartner->reviewer->name ?? '相手' }}さんからのレビュー</h5>
                                                {{-- ★ここを rating に変更 --}}
                                                <p><strong>評価:</strong> {{ $matching->reviewFromPartner->rating }} / 5</p>
                                                <p><strong>コメント:</strong> {{ $matching->reviewFromPartner->comment ?? 'コメントなし' }}</p>
                                            @else
                                                <p class="text-info">相手からのレビューはまだありません。</p>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted mt-3">マッチング完了後にレビューが表示されます。</p>
                                @endif
                                {{-- レビュー内容表示ブロックここまで --}}

                                {{-- メッセージページへのリンク --}}
                                <a href="{{ route('message.show', ['matchingId' => $matching->id]) }}" class="btn btn-primary btn-sm mt-3">メッセージを見る</a>

                                {{-- マッチングの承認・拒否・キャンセル・完了ボタン --}}
                                @if($matching->status == 0) {{-- 保留中の場合 --}}
                                    @if(Auth::id() == $matching->requestUser->id)
                                        <p style="color: gray;">承認待ちです。</p>
                                    @elseif(Auth::id() == $matching->offerUser->id)
                                        <form action="{{ route('matching.approve', $matching->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">承認する</button>
                                        </form>
                                        <form action="{{ route('matching.reject', $matching->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">拒否する</button>
                                        </form>
                                    @endif
                                @elseif($matching->status == 1) {{-- 承認済みの場合 --}}
                                    <p style="color: blue;">承認済みです。</p>
                                    @if(Auth::id() == $matching->offerUser->id || Auth::id() == $matching->requestUser->id)
                                        <form action="{{ route('matching.complete', $matching->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm">完了する</button>
                                        </form>
                                        <form action="{{ route('matching.cancel', $matching->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm">キャンセル</button>
                                        </form>
                                    @endif
                                @endif
                                
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('matching.history.index') }}" class="btn btn-info">全マッチング履歴を見る</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection