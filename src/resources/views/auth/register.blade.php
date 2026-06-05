@extends('layouts.app')

@section('title', '新規登録 | Career Inbox')

@section('content')
  <section class="page-head">
    <div>
      <p class="eyebrow">Create account</p>
      <h1>新規登録</h1>
      <p class="muted">転職活動の求人管理を始めるためのアカウントを作成します。</p>
    </div>
  </section>

  @if ($errors->any())
    <div class="error-box">
      入力内容を確認してください。
    </div>
  @endif

  <form class="panel" method="post" action="{{ route('register.store') }}">
    @csrf
    <div class="form-grid">
      <label class="span-2">
        名前
        <input name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
      </label>
      <label class="span-2">
        メールアドレス
        <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
      </label>
      <label>
        パスワード
        <input name="password" type="password" autocomplete="new-password" required>
      </label>
      <label>
        パスワード確認
        <input name="password_confirmation" type="password" autocomplete="new-password" required>
      </label>
      <div class="actions span-2">
        <button class="button" type="submit">登録して始める</button>
        <a class="button secondary" href="{{ route('login') }}">ログインへ戻る</a>
      </div>
    </div>
  </form>
@endsection
