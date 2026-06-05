<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Career Inbox') }}</title>
    <style>
      :root {
        --bg: #f5f7f8;
        --surface: #ffffff;
        --ink: #172026;
        --muted: #65727b;
        --line: #d8e0e4;
        --primary: #0f766e;
        --accent: #b45309;
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

      .shell {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        min-height: 100vh;
      }

      .sidebar {
        display: flex;
        flex-direction: column;
        gap: 28px;
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

      .brand strong,
      .brand span,
      .side-panel p,
      .eyebrow,
      .note {
        margin: 0;
      }

      .brand span,
      .side-panel p {
        color: #b8c8ca;
        font-size: 13px;
      }

      .nav {
        display: grid;
        gap: 8px;
      }

      .nav div {
        border-radius: 6px;
        padding: 11px 12px;
        background: rgba(255, 255, 255, 0.08);
        color: #d9e6e7;
      }

      .side-panel {
        display: grid;
        gap: 12px;
        margin-top: auto;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 8px;
        padding: 16px;
        background: rgba(255, 255, 255, 0.06);
      }

      .main {
        display: grid;
        align-content: start;
        gap: 22px;
        padding: 30px;
      }

      .hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
      }

      .eyebrow {
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
      }

      h1 {
        max-width: 860px;
        margin: 6px 0 0;
        font-size: clamp(30px, 5vw, 54px);
        line-height: 1.08;
        letter-spacing: 0;
      }

      .status {
        min-width: 220px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--surface);
        padding: 16px;
      }

      .status strong {
        display: block;
        margin-bottom: 6px;
        color: var(--primary);
      }

      .grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
      }

      .card {
        min-height: 160px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--surface);
        padding: 18px;
      }

      .card h2 {
        margin: 0 0 10px;
        font-size: 18px;
      }

      .card p,
      .note {
        color: var(--muted);
        line-height: 1.7;
      }

      .commands {
        display: grid;
        gap: 10px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: #102428;
        color: #f8fbfb;
        padding: 18px;
      }

      code {
        display: block;
        overflow-x: auto;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.08);
        padding: 12px;
        color: #d4f7ef;
      }

      .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        width: fit-content;
        border: 1px solid var(--primary);
        border-radius: 6px;
        background: var(--primary);
        color: #ffffff;
        font-weight: 700;
        padding: 0 14px;
        text-decoration: none;
      }

      @media (max-width: 900px) {
        .shell,
        .grid {
          grid-template-columns: 1fr;
        }

        .hero {
          flex-direction: column;
        }

        .status {
          width: 100%;
        }
      }
    </style>
  </head>
  <body>
    <div class="shell">
      <aside class="sidebar">
        <div class="brand">
          <div class="mark">CI</div>
          <div>
            <strong>Career Inbox</strong>
            <span>Laravel Docker App</span>
          </div>
        </div>
        <div class="nav">
          <div>受信求人</div>
          <div>希望条件</div>
          <div>Gmail 連携・検索</div>
          <div>マッチング</div>
        </div>
        <div class="side-panel">
          <strong>実装済み</strong>
          <p>求人CRUD、希望条件、マッチング、Gmail OAuth、本文抽出の土台まで動きます。</p>
        </div>
      </aside>

      <main class="main">
        <section class="hero">
          <div>
            <p class="eyebrow">Docker environment ready</p>
            <h1>求人メールを取り込み、応募したい会社を見つけやすくする。</h1>
          </div>
          <div class="status">
            <strong>Laravel 起動中</strong>
            <p class="note">PostgreSQL 接続前提の Docker 開発環境です。</p>
          </div>
        </section>

        <section class="grid">
          <article class="card">
            <h2>Gmail 取り込み</h2>
            <p>求人・スカウト・エージェントのメール本文から会社名、職種、業界、年収、勤務地、URLを抽出します。</p>
          </article>
          <article class="card">
            <h2>求人管理</h2>
            <p>未確認、気になる、応募したい、応募済み、見送りなどのステータスで、多様な職種の求人を整理します。</p>
          </article>
          <article class="card">
            <h2>マッチング</h2>
            <p>希望職種、業界、年収、勤務地、リモート、スキル・経験をもとに応募優先度を算出します。</p>
          </article>
        </section>

        <section class="commands">
          <strong>開発コマンド</strong>
          <code>docker compose up -d --build</code>
          <code>docker compose exec app php artisan migrate</code>
        </section>

        <section class="commands">
          <strong>アプリ画面</strong>
          <a class="button" href="{{ route('jobs.index') }}">受信求人を開く</a>
          <a class="button" href="{{ route('gmail.index') }}">Gmail 連携・検索を開く</a>
        </section>
      </main>
    </div>
  </body>
</html>
