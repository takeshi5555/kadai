@extends('layouts.app')

@section('title', 'マッチング申し込み')

@section('content')
    <h1>マッチング申し込み</h1>

    <h2>相手のスキル</h2>
    <p><strong>{{ $targetSkill->title }}</strong>（{{ $targetSkill->category }}）</p>
    <p>{{ $targetSkill->description }}</p>

    <h2>自分のスキルを選択</h2>
    <form method="POST" action="/matching/apply/confirm">
        @csrf
        <input type="hidden" name="receiving_skill_id" value="{{ $targetSkill->id }}">

        <label for="offering_skill_id">提供するスキル:</label><br>
        <select name="offering_skill_id" required>
            <option value="">選択してください</option>
            @foreach ($mySkills as $skill)
                <option value="{{ $skill->id }}">{{ $skill->title }}（{{ $skill->category }}）</option>
            @endforeach
        </select><br><br>
        <label>日時を選択:</label>
        <input type="datetime-local" name="scheduled_at" required>

        <button type="submit">確認画面へ</button>
    </form>
@endsection