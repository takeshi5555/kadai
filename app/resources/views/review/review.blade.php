@extends('layouts.app')

@section('title', 'レビュー投稿')

@section('content')
<h1>レビュー投稿</h1>

<p><strong>対象スキル:</strong> {{ $matching->offeringSkill->title }} / {{ $matching->receivingSkill->title }}</p>

<form method="POST" action="/review/{{ $matching->id }}">
    @csrf
    <label>評価 (1〜5): 
        <select name="rating" required>
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
    </label><br><br>

    <label>コメント:<br>
        <textarea name="comment" rows="4" cols="50" maxlength="1000"></textarea>
    </label><br><br>

    <button type="submit">送信</button>
</form>
@endsection
