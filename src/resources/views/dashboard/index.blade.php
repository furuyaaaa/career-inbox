@extends('layouts.app')

@section('title', 'ダッシュボード | Career Inbox')

@section('content')
  <header class="page-head">
    <div>
      <p class="eyebrow">Dashboard</p>
      <h1>ダッシュボード</h1>
      <p class="muted">受信求人の状態と、今日確認したい候補をまとめて見ます。</p>
    </div>
    <div class="actions">
      <a class="button secondary" href="{{ route('gmail.index') }}">Gmail 連携</a>
      <a class="button" href="{{ route('jobs.create') }}">求人を登録</a>
    </div>
  </header>

  <section class="grid">
    <article class="card job-card">
      <p class="eyebrow">Total</p>
      <h2>{{ $totalJobs }}件</h2>
      <p class="muted">保存済みの求人</p>
    </article>
    <article class="card job-card">
      <p class="eyebrow">Focus</p>
      <h2>{{ $focusCount }}件</h2>
      <p class="muted">気になる・応募したい求人</p>
    </article>
    <article class="card job-card">
      <p class="eyebrow">Progress</p>
      <h2>{{ $appliedCount }}件</h2>
      <p class="muted">応募済み・面談中・内定</p>
    </article>
    <article class="card job-card">
      <p class="eyebrow">Gmail</p>
      <h2>{{ $recentGmailJobs->count() }}件</h2>
      <p class="muted">最近のGmail取り込み求人</p>
    </article>
  </section>

  <section class="panel">
    <div class="job-top">
      <div>
        <h2>ステータス別件数</h2>
        <p class="muted">応募候補がどこに溜まっているかを確認します。</p>
      </div>
      <a class="button secondary" href="{{ route('jobs.index') }}">すべて見る</a>
    </div>
    <div class="pill-row" style="margin-top: 14px;">
      @foreach ($statusCounts as $status => $count)
        <span class="pill">{{ $status }} {{ $count }}件</span>
      @endforeach
    </div>
  </section>

  <section class="grid">
    <div class="panel">
      <div class="job-top">
        <div>
          <h2>マッチ上位</h2>
          <p class="muted">希望条件に近い求人から確認できます。</p>
        </div>
        <a class="button secondary" href="{{ route('jobs.index', ['sort' => 'match']) }}">マッチ順</a>
      </div>

      <div class="job-list" style="margin-top: 14px;">
        @forelse ($topJobs as $jobPost)
          <article class="card job-card">
            <div class="job-top">
              <div class="job-title">
                <a href="{{ route('jobs.show', $jobPost) }}">{{ $jobPost->title }}</a>
                <span class="muted">{{ $jobPost->company_name }}</span>
              </div>
              <span class="match-score">{{ $jobPost->match['score'] }}</span>
            </div>
            <p class="muted">{{ $jobPost->match['reasons'][0] }}</p>
          </article>
        @empty
          <p class="muted">まだ求人がありません。Gmailデモ取り込みか求人登録から始めましょう。</p>
        @endforelse
      </div>
    </div>

    <div class="panel">
      <div class="job-top">
        <div>
          <h2>最近のGmail求人</h2>
          <p class="muted">メールから取り込んだ求人を新しい順に確認します。</p>
        </div>
        <a class="button secondary" href="{{ route('gmail.index') }}">取り込み</a>
      </div>

      <div class="job-list" style="margin-top: 14px;">
        @forelse ($recentGmailJobs as $jobPost)
          <article class="card job-card">
            <div class="job-title">
              <a href="{{ route('jobs.show', $jobPost) }}">{{ $jobPost->title }}</a>
              <span class="muted">{{ $jobPost->company_name }} / {{ optional($jobPost->received_at)->format('Y-m-d') ?? '受信日未設定' }}</span>
            </div>
            <div class="pill-row">
              <span class="pill">{{ $jobPost->status }}</span>
              @if ($jobPost->occupation)<span class="pill">{{ $jobPost->occupation }}</span>@endif
              @if ($jobPost->remote_type)<span class="pill">{{ $jobPost->remote_type }}</span>@endif
            </div>
          </article>
        @empty
          <p class="muted">Gmail由来の求人はまだありません。</p>
        @endforelse
      </div>
    </div>
  </section>
@endsection
