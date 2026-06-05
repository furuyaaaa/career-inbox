@extends('layouts.app')

@section('title', 'Gmail 連携 | Career Inbox')

@section('content')
  <section class="page-head">
    <div>
      <p class="eyebrow">Gmail Integration</p>
      <h1>Gmail 連携</h1>
      <p class="muted">求人メールを検索し、Career Inbox の求人として取り込みます。</p>
    </div>
    <div class="actions">
      <form method="post" action="{{ route('gmail.demo-import') }}">
        @csrf
        <button class="button secondary" type="submit">デモ取り込み</button>
      </form>
      <a class="button" href="{{ route('gmail.connect') }}">Gmail を接続</a>
    </div>
  </section>

  @if ($errors->any())
    <div class="error-box">
      入力内容を確認してください。
    </div>
  @endif

  <section class="grid">
    <div class="panel">
      <h2>接続状態</h2>
      <div class="pill-row" style="margin-top: 12px;">
        <span @class(['pill', 'warning' => ! $configured])>
          {{ $configured ? 'OAuth 設定済み' : 'OAuth 未設定' }}
        </span>
        <span @class(['pill', 'warning' => ! $connection])>
          {{ $connection ? 'Gmail 接続済み' : '未接続' }}
        </span>
      </div>
      <p class="muted" style="margin-top: 14px;">
        @if ($connection)
          接続アカウント: {{ $connection->email ?? 'メールアドレス未取得' }} / 最終接続: {{ optional($connection->connected_at)->format('Y-m-d H:i') }}
        @else
          Google Cloud で OAuth クライアントを作成し、環境変数を設定すると実際の Gmail 読み取りが使えます。
        @endif
      </p>
    </div>

    <form class="panel" method="post" action="{{ route('gmail.settings.update') }}">
      @csrf
      @method('PUT')
      <h2>OAuth 設定</h2>
      <p class="muted" style="margin-top: 10px;">Google Cloud のOAuthクライアント情報を登録します。</p>
      <div class="form-grid" style="margin-top: 14px;">
        <label class="span-2">
          Client ID
          <input name="client_id" value="{{ old('client_id', $oauthSetting->client_id) }}" placeholder="xxxxx.apps.googleusercontent.com">
        </label>
        <label class="span-2">
          Client Secret
          <input name="client_secret" type="password" placeholder="{{ $oauthSetting->client_secret ? '保存済み。変更する場合のみ入力' : 'Google Cloud の Client Secret' }}">
        </label>
        <label class="span-2">
          リダイレクトURI
          <input name="redirect_uri" value="{{ old('redirect_uri', $oauthSetting->redirect_uri ?: url('/gmail/callback')) }}">
        </label>
        <div class="span-2">
          <p class="muted">Google Cloud の「承認済みのリダイレクト URI」に、上のリダイレクトURIを登録してください。</p>
        </div>
        <div class="actions span-2">
          <button class="button" type="submit">OAuth 設定を保存</button>
        </div>
      </div>
    </form>

    <form class="panel" method="post" action="{{ route('gmail.import') }}">
      @csrf
      <h2>メール検索</h2>
      <div class="form-grid" style="margin-top: 14px;">
        <label class="span-2">
          Gmail 検索クエリ
          <input name="query" value="{{ old('query', 'subject:(求人 OR スカウト OR 採用 OR 募集)') }}">
        </label>
        <label>
          取り込み件数
          <input name="limit" type="number" min="1" max="25" value="{{ old('limit', 10) }}">
        </label>
        <div class="actions" style="align-self: end;">
          <button class="button" type="submit">取り込む</button>
        </div>
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="page-head">
      <div>
        <h2>取り込み履歴</h2>
        <p class="muted">取り込んだメールと作成された求人を確認できます。</p>
      </div>
    </div>

    <div class="job-list" style="margin-top: 14px;">
      @forelse ($imports as $import)
        <article class="job-card card">
          <div class="job-top">
            <div class="job-title">
              <a href="{{ $import->jobPost ? route('jobs.show', $import->jobPost) : '#' }}">
                {{ $import->subject ?? '件名なし' }}
              </a>
              <span class="muted">{{ $import->sender ?? '送信者不明' }}</span>
            </div>
            <span class="pill">{{ $import->status }}</span>
          </div>
          <p class="muted">{{ $import->snippet }}</p>
          @if ($import->jobPost)
            <div class="pill-row">
              <span class="pill">{{ $import->jobPost->company_name }}</span>
              <span class="pill">{{ $import->jobPost->occupation ?? '職種未設定' }}</span>
              <span class="pill">{{ $import->jobPost->industry ?? '業界未設定' }}</span>
            </div>
          @endif
        </article>
      @empty
        <p class="muted">まだ取り込み履歴はありません。デモ取り込みで画面の流れを確認できます。</p>
      @endforelse
    </div>

    <div style="margin-top: 14px;">
      {{ $imports->links() }}
    </div>
  </section>
@endsection
