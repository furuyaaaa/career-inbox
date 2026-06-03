@extends('layouts.app')

@section('title', '受信求人 | Career Inbox')

@section('content')
  <header class="page-head">
    <div>
      <p class="eyebrow">Job posts</p>
      <h1>受信求人</h1>
      <p class="muted">Gmail や転職サイトから来た求人を、職種を問わず整理できる状態にします。</p>
    </div>
    <div class="actions">
      <a class="button secondary" href="{{ route('preferences.edit') }}">希望条件</a>
      <a class="button" href="{{ route('jobs.create') }}">求人を登録</a>
    </div>
  </header>

  <section class="panel">
    <h2>現在の希望条件</h2>
    <div class="pill-row" style="margin-top: 10px;">
      <span class="pill">年収 {{ $profile->desired_salary_min ?? '?' }}万円以上</span>
      @foreach ($profile->preferred_occupations ?? [] as $occupation)
        <span class="pill">{{ $occupation }}</span>
      @endforeach
      @foreach ($profile->preferred_industries ?? [] as $industry)
        <span class="pill">{{ $industry }}</span>
      @endforeach
      @foreach ($profile->preferred_locations ?? [] as $location)
        <span class="pill">{{ $location }}</span>
      @endforeach
      @foreach ($profile->preferred_technologies ?? [] as $technology)
        <span class="pill">{{ $technology }}</span>
      @endforeach
      @if ($profile->remote_required)
        <span class="pill warning">リモート優先</span>
      @endif
    </div>
  </section>

  <section class="panel">
    <form class="toolbar" method="get" action="{{ route('jobs.index') }}">
      <label>
        キーワード
        <input name="keyword" value="{{ request('keyword') }}" placeholder="会社名・求人名・職種・業界・勤務地">
      </label>
      <label>
        ステータス
        <select name="status">
          <option value="">すべて</option>
          @foreach ($statuses as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
          @endforeach
        </select>
      </label>
      <label>
        リモート
        <select name="remote_type">
          <option value="">すべて</option>
          @foreach ($remoteTypes as $remoteType)
            <option value="{{ $remoteType }}" @selected(request('remote_type') === $remoteType)>{{ $remoteType }}</option>
          @endforeach
        </select>
      </label>
      <label>
        並び順
        <select name="sort">
          <option value="">受信日順</option>
          <option value="match" @selected(request('sort') === 'match')>マッチ順</option>
        </select>
      </label>
      <button class="button" type="submit">絞り込み</button>
    </form>
  </section>

  <section class="job-list">
    @forelse ($jobPosts as $jobPost)
      <article class="card job-card">
        <div class="job-top">
          <div class="job-title">
            <a href="{{ route('jobs.show', $jobPost) }}">{{ $jobPost->title }}</a>
            <span class="muted">{{ $jobPost->company_name }} / {{ $jobPost->source ?? '未設定' }}</span>
          </div>
          <div class="actions">
            <span class="match-score">{{ $jobPost->match['score'] }}</span>
            <a class="button secondary" href="{{ route('jobs.edit', $jobPost) }}">編集</a>
          </div>
        </div>
        <p class="muted">{{ implode('。', $jobPost->match['reasons']) }}。</p>
        <div class="pill-row">
          <span class="pill">{{ $jobPost->status }}</span>
          <span class="pill warning">興味度 {{ $jobPost->interest_level }}</span>
          @if ($jobPost->occupation)<span class="pill">{{ $jobPost->occupation }}</span>@endif
          @if ($jobPost->industry)<span class="pill">{{ $jobPost->industry }}</span>@endif
          @if ($jobPost->location)<span class="pill">{{ $jobPost->location }}</span>@endif
          @if ($jobPost->remote_type)<span class="pill">{{ $jobPost->remote_type }}</span>@endif
          @if ($jobPost->salary_min || $jobPost->salary_max)
            <span class="pill">{{ $jobPost->salary_min ?? '?' }}-{{ $jobPost->salary_max ?? '?' }}万円</span>
          @endif
        </div>
        @if ($jobPost->technologies)
          <div class="pill-row">
            @foreach ($jobPost->technologies as $technology)
              <span class="pill">{{ $technology }}</span>
            @endforeach
          </div>
        @endif
      </article>
    @empty
      <section class="panel">
        <p class="muted">まだ求人がありません。まずは「求人を登録」から、気になる求人を入れてみましょう。</p>
      </section>
    @endforelse
  </section>

  {{ $jobPosts->links() }}
@endsection
