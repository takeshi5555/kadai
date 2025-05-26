@extends('layouts.app')

@section('title', 'マッチング履歴')

@section('content')
<h1>マッチング履歴</h1>

<h2>あなたが申請したマッチング</h2>
@forelse ($applied as $match)
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        {{-- 相手のユーザー名 (あなたが申請したので、相手はrecipient側) --}}
        <p><strong>相手のユーザー:</strong> {{ $match->recipient_user_id ? ($match->recipient->name ?? '不明') : '不明' }}</p> {{-- recipient リレーションを使用 --}}
        <p><strong>あなたが提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p>
        <p><strong>相手が提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p> {{-- 相手が提供するスキルなのでreceivingSkillを表示 --}}
        <p><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H:i') : '未定' }}</p>
        <p><strong>ステータス:</strong>
            @switch($match->status)
                @case(0) <span style="color:orange;">申請中</span> @break
                @case(1) <span style="color:green;">承認済み</span> @break
                @case(2) <span style="color:blue;">完了</span> @break
                @case(3) <span style="color:red;">拒否</span> @break
                @default 不明
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

        @if ($match->status === 2)
            {{-- 自分が書いたレビュー --}}
            @if ($match->myReview)
                <div style="margin-top:10px; padding:10px; background:#f9f9f9;">
                    <strong>あなたのレビュー:</strong><br>
                    評価: {{ $match->myReview->rating }} / 5<br>
                    コメント: {{ $match->myReview->comment ?? '（なし）' }}
                </div>
            @else
                <a href="/review/{{ $match->id }}">レビューを書く</a>
            @endif

            {{-- 相手が書いたレビュー --}}
            @if ($match->partnerReview)
                <div style="margin-top:10px; padding:10px; background:#e6ffe6;"> {{-- 相手のレビューは色を変えても良いでしょう --}}
                    <strong>相手のレビュー:</strong><br>
                    評価: {{ $match->partnerReview->rating }} / 5<br>
                    コメント: {{ $match->partnerReview->comment ?? '（なし）' }}
                </div>
            @else
                <p>相手からのレビューはまだありません。</p>
            @endif
        @endif
    </div>
@empty
    <p>申請したマッチングはありません。</p>
@endforelse

<hr>

<h2>あなたに申請されたマッチング</h2>
@forelse ($received as $match)
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        {{-- 相手のユーザー名 (あなたに申請したので、相手はapplicant側) --}}
        <p><strong>相手のユーザー:</strong> {{ $match->applicant_user_id ? ($match->applicant->name ?? '不明') : '不明' }}</p> {{-- applicant リレーションを使用 --}}
        <p><strong>あなたが提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p> {{-- あなたが提供するのはreceivingSkill --}}
        <p><strong>相手が提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p> {{-- 相手が提供するのはofferingSkill --}}
        <p><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H:i') : '未定' }}</p>
        <p><strong>ステータス:</strong>
            @switch($match->status)
                @case(0) <span style="color:orange;">申請中</span> @break
                @case(1) <span style="color:green;">承認済み</span> @break
                @case(2) <span style="color:blue;">完了</span> @break
                @case(3) <span style="color:red;">拒否</span> @break
                @default 不明
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

        @if ($match->status === 2)
            {{-- 自分が書いたレビュー --}}
            @if ($match->myReview)
                <div style="margin-top:10px; padding:10px; background:#f9f9f9;">
                    <strong>あなたのレビュー:</strong><br>
                    評価: {{ $match->myReview->rating }} / 5<br>
                    コメント: {{ $match->myReview->comment ?? '（なし）' }}
                </div>
            @else
                <a href="/review/{{ $match->id }}">レビューを書く</a>
            @endif

            {{-- 相手が書いたレビュー --}}
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
@empty
    <p>申請されたマッチングはありません。</p>
@endforelse
@endsection