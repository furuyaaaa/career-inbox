# Development Guidelines

Career Inbox は、求人メールやGmail連携情報を扱う転職活動管理アプリです。
実装では、機能追加の速さだけでなく、データ分離、読みやすさ、テストしやすさ、安全な外部連携を重視します。

## 基本方針

- Controller は薄く保ち、HTTPリクエストの受け口に集中させる。
- Gmail取り込み、求人抽出、マッチング計算などの業務ロジックは Service に寄せる。
- View は表示に必要な分岐に留め、複雑な条件判定やデータ加工を増やしすぎない。
- DB構造を変える時は Migration、Model、Factory、Seeder、Test を合わせて更新する。
- 個人情報やGmailデータを扱う処理では、ユーザーごとのデータ分離を最優先にする。

## SOLID

### Single Responsibility Principle

1つのクラスに複数の責務を持たせない。

- Controller は認証、バリデーション、リダイレクト、View返却に集中する。
- Gmail API通信は `GmailImportService` のような専用Serviceに閉じる。
- マッチング計算は `JobMatchScorer` のような専用Serviceに閉じる。
- 画面表示用の細かい文言やHTML構造は Blade に閉じる。

### Open/Closed Principle

既存処理を壊さずに拡張できる構成を優先する。

- マッチング条件を増やす時は、既存スコア条件を壊さず追加する。
- Gmail本文の抽出ルールを増やす時は、既存の抽出結果が変わりすぎないようテストを追加する。
- 候補ボタンや検索条件の追加は、保存形式との互換性を保つ。

### Liskov Substitution Principle

呼び出し側が、実装詳細に依存しすぎないようにする。

- Service の戻り値の型と意味を安定させる。
- `score()` のようなメソッドは、呼び出し側が扱いやすい一貫した配列やDTOを返す。
- nullや空配列の扱いを揃え、呼び出し側で過剰な例外対応をしなくて済むようにする。

### Interface Segregation Principle

大きすぎるServiceやControllerを避ける。

- Gmail認証、Gmail取り込み、求人抽出、マッチングは責務ごとに分ける。
- 1つのServiceが肥大化したら、抽出用ServiceやQuery用クラスへ分ける。
- Viewで使う候補リストが増えすぎたら、設定クラスや専用メソッドへ切り出す。

### Dependency Inversion Principle

外部APIや複雑な処理をControllerから直接呼ばない。

- Controller は Gmail API を直接叩かず、Service 経由で扱う。
- 外部API通信は `Http::fake()` でテストできる形にする。
- 将来的にGoogle以外のメール連携を増やす場合も、Controllerの変更を最小化する。

## Laravel実装ルール

- バリデーションは Controller または FormRequest にまとめる。
- 保存前の配列変換やCSV変換などは、Controller内の小さなprivateメソッドかServiceへ分ける。
- Eloquentのクエリは読みやすさを優先し、複雑になったらQuery用メソッドへ切り出す。
- Bladeでは、表示用の小さな `@php` は許容するが、業務ロジックは書かない。
- Seeder は再実行しても重複や一意制約エラーが起きないようにする。
- Factory はテストで自然なデータを作れる状態に保つ。

## セキュリティ方針

- Gmailの `access_token`、`refresh_token`、Client Secret をログに出さない。
- 本番ではデモログインを無効化する。
- 本番では `APP_DEBUG=false` を必ず設定する。
- OAuthスコープは必要最小限にする。
- Gmail本文を保存する場合は、保存範囲と削除方法を明確にする。
- ユーザーごとの `job_posts`、`preference_profiles`、`gmail_connections`、`gmail_imports` の分離を公開前に必須対応にする。
- 退会、Gmail連携解除、取り込みデータ削除を前提に設計する。
- Google API Services User Data Policy に沿ったプライバシーポリシーとデータ削除導線を用意する。

## テスト方針

- CRUDはFeature Testで確認する。
- Gmail API通信は実APIではなく `Http::fake()` で確認する。
- Gmail OAuth設定やcallbackなど、失敗しやすい導線はFeature Testを追加する。
- マッチングロジックは、スコア順とスコア理由をテストする。
- バグ修正時は、再発防止テストを追加する。
- UI文言を変更した場合、重要なナビゲーションやボタンはFeature Testで検知できるようにする。

## 公開前チェック

- デモログインを本番で無効化する。
- 全テーブルに必要な `user_id` を追加し、クエリをユーザー単位に限定する。
- Gmail連携解除とトークン削除を実装する。
- 取り込み済みメールと求人データの削除導線を実装する。
- HTTPS、Cookie secure設定、ログ出力、バックアップ暗号化を確認する。
- プライバシーポリシー、利用規約、問い合わせ先を用意する。
