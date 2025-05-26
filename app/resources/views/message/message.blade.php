@extends('layouts.app')

@section('title', 'メッセージ')

@section('content')
<h1>メッセージ（マッチングID: {{ $matching->id }})</h1>

<div style="border:1px solid #ccc; padding:10px; max-height:400px; overflow-y:auto;">
@forelse ($messages as $msg) {{-- ★修正: $content から $messages に変更 --}}
    <div style="margin-bottom: 10px; {{ $msg->sender_id === Auth::id() ? 'text-align: right;' : 'text-align: left;' }}"> {{-- 送信者によって右寄せ・左寄せ --}}
        @if ($msg->sender_id !== Auth::id())
            <strong>{{ $msg->sender->name }}:</strong><br>
        @else
            <strong>あなた:</strong><br> {{-- 自分のメッセージは「あなた」と表示 --}}
        @endif
        {{ $msg->content }}<br>
        <small>{{ $msg->created_at->format('Y-m-d H:i') }}</small>
    </div>
@empty
    <p>まだメッセージはありません。</p>
@endforelse
</div>

<form method="POST" action="/message/{{ $matching->id }}" style="margin-top:20px;">
    @csrf
    <textarea name="content" rows="3" cols="60" required placeholder="メッセージを入力してください"></textarea><br>
    <button type="submit">送信</button>
</form>
@endsection
