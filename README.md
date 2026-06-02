# Career Inbox

転職サイトやエージェントから届く求人メールを整理し、自分が応募したい会社を見つけやすくするための転職活動管理アプリです。

## 背景

転職活動では、転職サイト・スカウトサービス・エージェントから多くの求人メールが届きます。

その中から自分に合う会社を探そうとしても、メールの量が多く、重要な求人が埋もれやすいという課題があります。

Career Inbox は、求人メールや会社情報を一元管理し、条件・ステータス・興味度で整理することで、応募したい会社を見つけやすくすることを目的としています。

## プロトタイプ

まずは画面イメージとマッチング体験を確認するため、静的なプロトタイプを用意しています。

- `index.html` をブラウザで開くと利用できます
- 「サンプル同期」で Gmail から求人メールを取り込んだ想定の求人が追加されます
- 希望年収、勤務地、リモート可否、技術、除外キーワードを変更するとスコアが再計算されます
- ステータスを変更して、応募候補を整理できます

実際の Gmail 連携では、Gmail API の OAuth 認証を使い、求人メールらしいメッセージだけを取得して `job_posts` に保存する想定です。

## Docker 開発環境

Laravel + PostgreSQL を Docker Compose で起動できます。

### 起動

```bash
docker compose up -d --build
```

ブラウザで以下を開きます。

```text
http://localhost:8080
```

### 初期セットアップ

初回起動後、DB マイグレーションを実行します。

```bash
docker compose exec app php artisan migrate
```

フロントエンドの Vite 開発サーバーも使う場合は、`frontend` profile を付けて起動します。

```bash
docker compose --profile frontend up -d --build
```

### サービス構成

- `web`: Nginx
- `app`: PHP-FPM / Laravel
- `db`: PostgreSQL
- `node`: Vite 開発サーバー

## 想定ユーザー

- 転職活動中のエンジニア
- 複数の転職サイトやエージェントを利用している人
- 求人メールを整理して、応募先候補を比較したい人

## 主な機能

### 求人管理

- 求人情報の登録・編集・削除
- 会社名、求人タイトル、勤務地、年収、雇用形態の管理
- 使用技術、リモート可否、求人URL、メモの管理

### ステータス管理

- 未確認
- 気になる
- 応募したい
- 応募済み
- 面談中
- 見送り
- 内定
- 辞退

### 検索・絞り込み

- 会社名で検索
- ステータスで絞り込み
- 年収で絞り込み
- リモート可否で絞り込み
- 使用技術で絞り込み

### マッチング機能

Python を使って、自分の希望条件と求人情報を比較し、応募優先度を算出する機能を実装予定です。

例:

- 希望年収との一致度
- 使用技術との一致度
- 勤務地・リモート条件との一致度
- 興味度や応募ステータスを含めたスコアリング

### Gmail 連携

- OAuth で Gmail アカウントを接続
- `from:` や `subject:`、キーワードで求人メール候補を検索
- メール本文から会社名、求人タイトル、年収、勤務地、技術、URL を抽出
- 取り込み済みメールの重複登録を防止
- 抽出結果をユーザーが修正できる下書き状態で保存

## 技術構成

### アプリケーション

- Laravel
- PHP
- Blade
- Tailwind CSS

### データベース

- PostgreSQL

### マッチング処理

- Python

### 開発・管理

- Git
- GitHub

## データ設計案

初期段階では、シンプルに `job_posts` テーブルを中心に作成します。

```text
users
  id
  name
  email
  password

job_posts
  id
  user_id
  company_name
  title
  source
  agent_name
  location
  salary_min
  salary_max
  employment_type
  remote_type
  technologies
  status
  interest_level
  url
  received_at
  memo
  created_at
  updated_at
```

将来的には、会社情報やエージェント情報を別テーブルに分ける予定です。

```text
companies
agents
job_posts
applications
matching_scores
```

## 開発予定

### Phase 1: 基本CRUD

- Laravel プロジェクト作成
- 認証機能の導入
- 求人情報CRUD
- PostgreSQL 接続

### Phase 2: Gmail 取り込み

- Google Cloud で OAuth クライアントを作成
- Gmail API の読み取り権限を設定
- 求人メール検索条件を保存
- メール本文から求人情報を抽出
- 取り込み候補の確認画面を作成

### Phase 3: 検索・整理

- ステータス別絞り込み
- キーワード検索
- 年収・リモート条件での絞り込み
- 気になる度の管理

### Phase 4: Python マッチング

- 希望条件の登録
- 求人情報とのマッチングスコア計算
- スコア順の並び替え

### Phase 5: ポートフォリオ強化

- ダッシュボード
- グラフ表示
- README の拡充
- デモ用データ作成

## このアプリで学ぶこと

- Laravel による CRUD アプリ開発
- PostgreSQL を使ったデータ管理
- 認証機能とユーザーごとのデータ管理
- Python によるマッチングロジック
- 転職活動という実体験に基づいた課題解決型アプリの設計
