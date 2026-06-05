<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Career Inbox'))</title>
    <style>
      :root {
        --bg: #f5f7f8;
        --surface: #ffffff;
        --ink: #172026;
        --muted: #65727b;
        --line: #d8e0e4;
        --primary: #0f766e;
        --primary-dark: #0b5f59;
        --danger: #b91c1c;
        --warning: #b45309;
        --shadow: 0 18px 45px rgba(23, 32, 38, 0.08);
      }

      * {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        min-height: 100vh;
        background: var(--bg);
        color: var(--ink);
        font-family: "Yu Gothic UI", "Hiragino Sans", Meiryo, sans-serif;
      }

      a {
        color: inherit;
        text-decoration: none;
      }

      button,
      input,
      select,
      textarea {
        font: inherit;
      }

      .shell {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        min-height: 100vh;
      }

      .sidebar {
        display: flex;
        flex-direction: column;
        gap: 26px;
        padding: 24px;
        background: #102428;
        color: #f8fbfb;
      }

      .brand {
        display: flex;
        align-items: center;
        gap: 12px;
      }

      .mark {
        display: grid;
        width: 44px;
        height: 44px;
        place-items: center;
        border-radius: 8px;
        background: #1f3d3f;
        color: #d4f7ef;
        font-weight: 800;
      }

      .brand p,
      .side-note p,
      .eyebrow,
      .muted {
        margin: 0;
      }

      .brand strong {
        display: block;
      }

      .brand p,
      .side-note p {
        color: #b8c8ca;
        font-size: 13px;
      }

      .nav {
        display: grid;
        gap: 8px;
      }

      .nav a {
        border-radius: 6px;
        padding: 11px 12px;
        color: #d9e6e7;
      }

      .nav a.active,
      .nav a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
      }

      .side-note {
        display: grid;
        gap: 10px;
        margin-top: auto;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 8px;
        padding: 16px;
        background: rgba(255, 255, 255, 0.06);
      }

      .account-box {
        display: grid;
        gap: 10px;
        margin-top: auto;
      }

      .account-box form {
        margin: 0;
      }

      .sidebar .button {
        width: 100%;
        border-color: rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
      }

      .main {
        display: grid;
        align-content: start;
        gap: 18px;
        padding: 30px;
      }

      .page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
      }

      .eyebrow {
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
      }

      h1 {
        margin: 4px 0 0;
        font-size: clamp(28px, 4vw, 44px);
        line-height: 1.1;
        letter-spacing: 0;
      }

      h2,
      h3 {
        margin: 0;
      }

      .muted {
        color: var(--muted);
        line-height: 1.7;
      }

      .panel,
      .card {
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--surface);
        box-shadow: var(--shadow);
      }

      .panel {
        padding: 18px;
      }

      .toolbar {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 160px 160px 150px auto;
        gap: 10px;
        align-items: end;
      }

      label {
        display: grid;
        gap: 7px;
        color: #344249;
        font-size: 13px;
        font-weight: 700;
      }

      input,
      select,
      textarea {
        width: 100%;
        min-height: 40px;
        border: 1px solid var(--line);
        border-radius: 6px;
        background: #fbfcfc;
        color: var(--ink);
        padding: 0 10px;
      }

      textarea {
        min-height: 110px;
        padding: 10px;
        resize: vertical;
      }

      .button,
      button.button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        border: 1px solid var(--primary);
        border-radius: 6px;
        background: var(--primary);
        color: #ffffff;
        cursor: pointer;
        font-weight: 700;
        padding: 0 14px;
      }

      .button:hover {
        background: var(--primary-dark);
      }

      .button.secondary {
        border-color: var(--line);
        background: var(--surface);
        color: var(--ink);
      }

      .button.danger {
        border-color: var(--danger);
        background: var(--danger);
      }

      .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
      }

      .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
      }

      .form-grid .span-2 {
        grid-column: 1 / -1;
      }

      .job-list {
        display: grid;
        gap: 12px;
      }

      .job-card {
        display: grid;
        gap: 12px;
        padding: 18px;
      }

      .job-top {
        display: flex;
        justify-content: space-between;
        gap: 14px;
      }

      .job-title {
        display: grid;
        gap: 4px;
      }

      .job-title a {
        font-size: 18px;
        font-weight: 800;
      }

      .pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .pill {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        background: #edf4f2;
        color: #23514d;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 700;
      }

      .pill.warning {
        background: #fff3df;
        color: var(--warning);
      }

      .option-group {
        display: grid;
        gap: 10px;
      }

      .option-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .option-button {
        min-height: 34px;
        border: 1px solid var(--line);
        border-radius: 999px;
        background: #ffffff;
        color: #344249;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        padding: 0 12px;
      }

      .option-button.active {
        border-color: var(--primary);
        background: #edf4f2;
        color: var(--primary);
      }

      .match-score {
        display: grid;
        width: 48px;
        height: 48px;
        place-items: center;
        border-radius: 50%;
        background: #edf4f2;
        color: var(--primary);
        font-size: 18px;
        font-weight: 900;
      }

      .flash {
        border: 1px solid #b7ead9;
        border-radius: 8px;
        background: #e9fbf3;
        color: #145b43;
        padding: 12px 14px;
      }

      .error-box {
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fef2f2;
        color: #7f1d1d;
        padding: 12px 14px;
      }

      .pagination {
        display: flex;
        gap: 8px;
      }

      @media (max-width: 940px) {
        .shell,
        .grid,
        .form-grid,
        .toolbar {
          grid-template-columns: 1fr;
        }

        .page-head,
        .job-top {
          flex-direction: column;
        }
      }
    </style>
  </head>
  <body>
    <div class="shell">
      <aside class="sidebar">
        <a class="brand" href="{{ auth()->check() ? route('jobs.index') : route('login') }}">
          <span class="mark">CI</span>
          <span>
            <strong>Career Inbox</strong>
            <p>求人メール管理</p>
          </span>
        </a>
        <nav class="nav">
          @auth
            <a href="{{ route('jobs.index') }}" @class(['active' => request()->routeIs('jobs.*')])>受信求人</a>
            <a href="{{ route('preferences.edit') }}" @class(['active' => request()->routeIs('preferences.*')])>希望条件</a>
            <a href="{{ route('gmail.index') }}" @class(['active' => request()->routeIs('gmail.*')])>Gmail 連携・検索</a>
            <a href="{{ route('jobs.index', ['sort' => 'match']) }}">マッチング</a>
          @else
            <a href="{{ route('login') }}" @class(['active' => request()->routeIs('login')])>ログイン</a>
            <a href="{{ route('register') }}" @class(['active' => request()->routeIs('register')])>新規登録</a>
          @endauth
        </nav>
        <div class="account-box">
          @auth
            <div class="side-note">
              <strong>{{ auth()->user()->name }}</strong>
              <p>{{ auth()->user()->email }}</p>
            </div>
            <form method="post" action="{{ route('logout') }}">
              @csrf
              <button class="button secondary" type="submit">ログアウト</button>
            </form>
          @else
            <div class="side-note">
              <strong>Career Inbox</strong>
              <p>ログインして求人メールと希望条件を管理します。</p>
            </div>
          @endauth
        </div>
      </aside>

      <main class="main">
        @if (session('status'))
          <div class="flash">{{ session('status') }}</div>
        @endif

        @yield('content')
      </main>
    </div>
  </body>
</html>
