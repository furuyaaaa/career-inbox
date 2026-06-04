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
        <a class="button secondary" href="{{ route('register') }}">新規登録</a>
      </div>
    </div>
  </form>
@endsection
