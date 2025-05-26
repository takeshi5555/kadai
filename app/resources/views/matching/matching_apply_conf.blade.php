@extends('layouts.app')

@section('title', 'マッチング申し込み確認')

@section('content')
<h1>マッチング申し込み確認</h1>

<h2>相手のスキル</h2>
<p><strong>{{ $receiving->title }}</strong>（{{ $receiving->category }}）</p>
<p>{{ $receiving->description }}</p>

<h2>あなたのスキル</h2>
<p><strong>{{ $offering->title }}</strong>（{{ $offering->category }}）</p>
<p>{{ $offering->description }}</p>

<h2>予約日時</h2>
<p>{{ \Carbon\Carbon::parse($scheduledAt)->format('Y年m月d日 H:i') }}</p>

<form method="POST" action="/matching/apply/execute">
    @csrf
    <input type="hidden" name="offering_skill_id" value="{{ $offeringId }}">
    <input type="hidden" name="receiving_skill_id" value="{{ $receivingId }}">
    <input type="hidden" name="scheduled_at" value="{{ $scheduledAt }}">
    <button type="submit">申し込みを確定</button>
    <a href="/skill/detail/{{ $receiving->id }}"><button type="button">キャンセル</button></a>
</form>

@endsection