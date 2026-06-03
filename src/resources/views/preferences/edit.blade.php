@extends('layouts.app')

@section('title', '希望条件 | Career Inbox')

@section('content')
  <header class="page-head">
    <div>
      <p class="eyebrow">Preferences</p>
      <h1>希望条件</h1>
      <p class="muted">マッチングスコアの基準になる条件です。職種を問わず、カンマ区切りで複数指定できます。</p>
    </div>
    <a class="button secondary" href="{{ route('jobs.index', ['sort' => 'match']) }}">マッチ順を見る</a>
  </header>

  <form class="panel form-grid" method="post" action="{{ route('preferences.update') }}">
    @csrf
    @method('PUT')

    @if ($errors->any())
      <div class="error-box span-2">入力内容を確認してください。</div>
    @endif

    <label>
      希望年収下限（万円）
      <input name="desired_salary_min" type="number" min="0" max="3000" value="{{ old('desired_salary_min', $profile->desired_salary_min) }}">
      @error('desired_salary_min')<span class="muted">{{ $message }}</span>@enderror
    </label>

    <label>
      リモートを優先
      <select name="remote_required">
        <option value="1" @selected(old('remote_required', $profile->remote_required) == true)>はい</option>
        <option value="0" @selected(old('remote_required', $profile->remote_required) == false)>いいえ</option>
      </select>
    </label>

    <label class="span-2">
      希望職種カテゴリ
      <input name="preferred_occupations_text" value="{{ old('preferred_occupations_text', implode(', ', $profile->preferred_occupations ?? [])) }}" placeholder="営業, マーケティング, 経理, カスタマーサクセス">
    </label>

    <label class="span-2">
      希望業界
      <input name="preferred_industries_text" value="{{ old('preferred_industries_text', implode(', ', $profile->preferred_industries ?? [])) }}" placeholder="IT, 人材, メーカー, 医療, 教育">
    </label>

    <label class="span-2">
      希望勤務地
      <input name="preferred_locations_text" value="{{ old('preferred_locations_text', implode(', ', $profile->preferred_locations ?? [])) }}" placeholder="東京, 全国">
    </label>

    <label class="span-2">
      希望リモート条件
      <input name="preferred_remote_types_text" value="{{ old('preferred_remote_types_text', implode(', ', $profile->preferred_remote_types ?? [])) }}" placeholder="フルリモート, ハイブリッド, 週3リモート">
    </label>

    <label class="span-2">
      活かしたいスキル・経験キーワード
      <input name="preferred_technologies_text" value="{{ old('preferred_technologies_text', implode(', ', $profile->preferred_technologies ?? [])) }}" placeholder="法人営業, CRM, データ分析, 経理, 英語, Laravel">
    </label>

    <label class="span-2">
      除外キーワード
      <input name="excluded_keywords_text" value="{{ old('excluded_keywords_text', implode(', ', $profile->excluded_keywords ?? [])) }}" placeholder="SES, 常駐のみ">
    </label>

    <div class="actions span-2">
      <button class="button" type="submit">保存する</button>
      <a class="button secondary" href="{{ route('jobs.index') }}">キャンセル</a>
    </div>
  </form>
@endsection
