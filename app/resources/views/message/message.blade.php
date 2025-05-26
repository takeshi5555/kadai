@extends('layouts.app')

@section('title', 'メッセージ')

@section('content')
<h1>メッセージ（マッチングID: {{ $matching->id }})</h1>

<div style="border:1px solid #ccc; padding:10px; max-height:400px; overflow-y:auto;">
@foreach ($messages as $msg)
    <div style="margin-bottom: 10px;">
        <strong>{{ $msg->sender->name }}:</strong><br>
        {{ $msg->content }}<br>  {{-- ← message → content --}}
        <small>{{ $msg->created_at->format('Y-m-d H:i') }}</small>
    </div>
@endforeach
</div>

<form method="POST" action="/message/{{ $matching->id }}" style="margin-top:20px;">
    @csrf
    <textarea name="content" rows="3" cols="60" required></textarea><br>
    <button type="submit">送信</button>
</form>
@endsection
