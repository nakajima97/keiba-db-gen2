# バックエンド開発環境

## 前提

- Docker / Docker Compose がインストール済みであること
- `source/` ディレクトリで作業すること

## セットアップ

```bash
cd source

# 依存パッケージのインストール（PHP）
composer install

# 環境変数ファイルの作成
cp .env.example .env

# アプリケーションキーの生成
./vendor/bin/sail artisan key:generate

# コンテナ起動
./vendor/bin/sail up -d

# マイグレーション実行
./vendor/bin/sail artisan migrate

# シーダー実行（初期データが必要な場合）
./vendor/bin/sail artisan db:seed
```

## コンテナ構成

`compose.yaml` で定義されている。

| サービス | 内容 |
|---------|------|
| `laravel.test` | PHP 8.5 アプリケーションサーバー（ポート: 80） |
| `mysql` | MySQL 8.4（ポート: 3306） |

Vite 開発サーバーはポート 5173 でも公開される。

## 開発サーバー起動

```bash
./vendor/bin/sail up -d
```

Laravel + Vite をまとめて起動する場合:

```bash
./vendor/bin/sail composer run dev
```

## コード品質

| コマンド | 内容 |
|---------|------|
| `./vendor/bin/sail composer run lint` | Pint でlint（自動修正あり） |
| `./vendor/bin/sail composer run lint:check` | Pint でlint（チェックのみ） |

## テスト

```bash
./vendor/bin/sail artisan test
```

Pest を使用。設定ファイル: `source/phpunit.xml`  
テストDBは Sail 起動時に自動作成される（`testing` データベース）。

## Artisan コマンド

```bash
# ルーティング確認
./vendor/bin/sail artisan route:list

# モデル・マイグレーション生成
./vendor/bin/sail artisan make:model ModelName -m

# キャッシュクリア
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
```

## 環境変数（主要項目）

`.env.example` を参照。DB接続は Sail 起動時に自動設定される。

| 変数 | デフォルト値 |
|------|-------------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `mysql` |
| `DB_PORT` | `3306` |
| `APP_PORT` | `80` |
| `VITE_PORT` | `5173` |
