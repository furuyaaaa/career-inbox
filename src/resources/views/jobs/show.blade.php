@extends('layouts.app')

@section('title', $jobPost->company_name . ' | Career Inbox')

@section('content')
  <header class="page-head">
    <div>
      <p class="eyebrow">Job detail</p>
      <h1>{{ $jobPost->company_name }}</h1>
      <p class="muted">{{ $jobPost->title }}</p>
    </div>
    <div class="actions">
      <a class="button secondary" href="{{ route('jobs.index') }}">一覧へ戻る</a>
      <a class="button" href="{{ route('jobs.edit', $jobPost) }}">編集</a>
      <form method="post" action="{{ route('jobs.destroy', $jobPost) }}">
        @csrf
        @method('DELETE')
        <button class="button danger" type="submit">削除</button>
      </form>
    </div>
  </header>

  <section class="grid">
    <article class="panel">
      <h2>基本情報</h2>
      <p class="muted">掲載元: {{ $jobPost->source ?? '未設定' }}</p>
      <p class="muted">エージェント: {{ $jobPost->agent_name ?? '未設定' }}</p>
      <p class="muted">職種カテゴリ: {{ $jobPost->occupation ?? '未設定' }}</p>
      <p class="muted">業界: {{ $jobPost->industry ?? '未設定' }}</p>
      <p class="muted">勤務地: {{ $jobPost->location ?? '未設定' }}</p>
      <p class="muted">雇用形態: {{ $jobPost->employment_type ?? '未設定' }}</p>
      <p class="muted">受信日: {{ optional($jobPost->received_at)->format('Y-m-d') ?? '未設定' }}</p>
    </article>

    <article class="panel">
      <h2>条件</h2>
      <p class="muted">ステータス: {{ $jobPost->status }}</p>
      <p class="muted">興味度: {{ $jobPost->interest_level }}</p>
      <p class="muted">リモート: {{ $jobPost->remote_type ?? '未設定' }}</p>
      <p class="muted">年収: {{ $jobPost->salary_min ?? '?' }}-{{ $jobPost->salary_max ?? '?' }}万円</p>
    </article>
  </section>

  <section class="panel">
    <h2>スキル・経験キーワード</h2>
    <div class="pill-row">
      @forelse ($jobPost->technologies ?? [] as $technology)
        <span class="pill">{{ $technology }}</span>
      @empty
        <p class="muted">未設定</p>
      @endforelse
    </div>
  </section>

  <section class="panel">
    <h2>メモ</h2>
    <p class="muted">{{ $jobPost->memo ?: '未設定' }}</p>
    @if ($jobPost->url)
      <p><a class="button secondary" href="{{ $jobPost->url }}" target="_blank" rel="noreferrer">求人URLを開く</a></p>
    @endif
  </section>
@endsection
