@extends('layouts.app')

@section('title', 'ログイン | Career Inbox')

@section('content')
  <section class="page-head">
    <div>
      <p class="eyebrow">Sign in</p>
      <h1>ログイン</h1>
      <p class="muted">求人メール、希望条件、Gmail連携を自分のアカウントで管理します。</p>
    </div>
  </section>

  @if ($errors->any())
    <div class="error-box">
      メールアドレスとパスワードを確認してください。
    </div>
  @endif

  <form class="panel" method="post" action="{{ route('login.authenticate') }}">
    @csrf
    <div class="form-grid">
      <div class="flash span-2">
        デモ確認は <strong>test@example.com</strong> / <strong>password</strong> でログインできます。入力せずに進む場合は「デモユーザーで入る」を押してください。
      </div>
      <label class="span-2">
        メールアドレス
        <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
      </label>
      <label class="span-2">
        パスワード
        <input name="password" type="password" autocomplete="current-password" required>
      </label>
      <label>
        <span>
          <input name="remember" type="checkbox" value="1" style="width: auto; min-height: auto;">
          ログイン状態を保持
        </span>
      </label>
      <div class="actions" style="align-self: end;">
        <button class="button" type="submit">ログイン</button>
        <button class="button secondary" form="demo-login-form" type="submit">デモユーザーで入る</button>
        <a class="button secondary" href="{{ route('register') }}">新規登録</a>
      </div>
    </div>
  </form>

  <form id="demo-login-form" method="post" action="{{ route('login.demo') }}">
    @csrf
  </form>
@endsection
