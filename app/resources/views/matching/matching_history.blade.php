
@extends('layouts.app')

@section('title', 'マッチング履歴')

@section('content')
<h1>マッチング履歴</h1>

<h2>あなたが申請したマッチング</h2>
@forelse ($applied as $match)
    @if (in_array($match->status, [0, 1, 2, 3]))
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <p><strong>相手のユーザー:</strong> {{ $match->receivingSkill->user->name ?? '不明' }}</p>
            <p><strong>あなたが提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p>
            <p><strong>相手が提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p>
            <p><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H:i') : '未定' }}</p>
            <p><strong>ステータス:</strong>
                @switch($match->status)
                    @case(0) <span style="color:orange;">申請中</span> @break
                    @case(1) <span style="color:green;">承認済み</span> @break
                    @case(2) <span style="color:blue;">完了</span> @break
                    @case(3) <span style="color:red;">拒否</span> @break
                @endswitch
            </p>
            @if ($match->status === 0)
                <form method="POST" action="/matching/{{ $match->id }}/cancel" onsubmit="return confirm('本当に取り消しますか？');">
                    @csrf
                    <button type="submit">申請取り消し</button>
                </form>
            @endif

            @if ($match->status === 1)
                <form method="POST" action="/matching/{{ $match->id }}/complete" style="display:inline;" onsubmit="return confirm('このマッチングを完了しますか？');">
                    @csrf
                    <button type="submit">完了</button>
                </form>
            @endif

            {{-- メッセージリンクの追加 --}}
            @if (in_array($match->status, [0, 1])) {{-- 申請中または承認済みの場合はメッセージ可能 --}}
                <a href="/message/{{ $match->id }}"><button type="button">メッセージ</button></a>
            @endif

            @if ($match->status === 2)
                @if ($match->myReview)
                    <div style="margin-top:10px; padding:10px; background:#f9f9f9;">
                        <strong>あなたのレビュー:</strong><br>
                        評価: {{ $match->myReview->rating }} / 5<br>
                        コメント: {{ $match->myReview->comment ?? '（なし）' }}
                    </div>
                @else
                    <a href="/review/{{ $match->id }}">レビューを書く</a>
                @endif
                @if ($match->partnerReview)
                    <div style="margin-top:10px; padding:10px; background:#e6ffe6;">
                        <strong>相手のレビュー:</strong><br>
                        評価: {{ $match->partnerReview->rating }} / 5<br>
                        コメント: {{ $match->partnerReview->comment ?? '（なし）' }}
                    </div>
                @else
                    <p>相手からのレビューはまだありません。</p>
                @endif
            @endif
        </div>
    @endif
@empty
    <p>申請したマッチングはありません。</p>
@endforelse

<hr>

<h2>あなたに申請されたマッチング</h2>
@forelse ($received as $match)
    @if (in_array($match->status, [0, 1, 2, 3]))
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <p><strong>相手のユーザー:</strong> {{ $match->offeringSkill->user->name ?? '不明' }}</p>
            <p><strong>あなたが提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p>
            <p><strong>相手が提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p>
            <p><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H:i') : '未定' }}</p>
            <p><strong>ステータス:</strong>
                @switch($match->status)
                    @case(0) <span style="color:orange;">申請中</span> @break
                    @case(1) <span style="color:green;">承認済み</span> @break
                    @case(2) <span style="color:blue;">完了</span> @break
                    @case(3) <span style="color:red;">拒否</span> @break
                @endswitch
            </p>

            @if ($match->status === 0)
                <form method="POST" action="/matching/{{ $match->id }}/approve" style="display:inline;">
                    @csrf
                    <button type="submit">承認</button>
                </form>
                <form method="POST" action="/matching/{{ $match->id }}/reject" style="display:inline;">
                    @csrf
                    <button type="submit">拒否</button>
                </form>
            @endif

            @if ($match->status === 1)
                <form method="POST" action="/matching/{{ $match->id }}/complete" style="display:inline;" onsubmit="return confirm('このマッチングを完了しますか？');">
                    @csrf
                    <button type="submit">完了</button>
                </form>
            @endif

            {{-- メッセージリンクの追加 --}}
            @if (in_array($match->status, [0, 1])) {{-- 申請中または承認済みの場合はメッセージ可能 --}}
                <a href="/message/{{ $match->id }}"><button type="button">メッセージ</button></a>
            @endif

            @if ($match->status === 2)
                @if ($match->myReview)
                    <div style="margin-top:10px; padding:10px; background:#f9f9f9;">
                        <strong>あなたのレビュー:</strong><br>
                        評価: {{ $match->myReview->rating }} / 5<br>
                        コメント: {{ $match->myReview->comment ?? '（なし）' }}
                    </div>
                @else
                    <a href="/review/{{ $match->id }}">レビューを書く</a>
                @endif
                @if ($match->partnerReview)
                    <div style="margin-top:10px; padding:10px; background:#e6ffe6;">
                        <strong>相手のレビュー:</strong><br>
                        評価: {{ $match->partnerReview->rating }} / 5<br>
                        コメント: {{ $match->partnerReview->comment ?? '（なし）' }}
                    </div>
                @else
                    <p>相手からのレビューはまだありません。</p>
                @endif
            @endif
        </div>
    @endif
@empty
    <p>申請されたマッチングはありません。</p>
@endforelse
@endsection