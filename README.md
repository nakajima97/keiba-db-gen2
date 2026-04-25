# keiba-db-gen2
## 概要
個人利用目的の競馬DB

## 開発
開発フローは以下
https://github.com/nakajima97/ai-driven-development

## ドキュメント
docs配下を確認

## セットアップ

### 初回のみ

~~~bash
# 移動（のちに記載したコマンドの前提条件）
cd ./source

# .env を作成
cp .env.example .env

# vendor/ をインストール（使い捨てコンテナ経由）
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs

# Docker イメージのビルド
./vendor/bin/sail build --no-cache

# コンテナ起動
./vendor/bin/sail up -d

# アプリケーションキーの生成
./vendor/bin/sail artisan key:generate

# マイグレーション
./vendor/bin/sail artisan migrate

# シード
./vendor/bin/sail artisan db:seed

# フロント依存関係のインストール
pnpm install
~~~

### 通常起動

~~~bash
cd ./source

# コンテナ起動
./vendor/bin/sail up -d

# フロント
pnpm dev

# 停止
./vendor/bin/sail down

# フロントリント
pnpm lint

# フロントフォーマット
pnpm format
~~~

## ユーザーの作成
```bash
php artisan app:create-user
```

## git worktree を使った並列開発

Claude Code エージェントで複数 issue を並列実装する際に使用する。

### worktree 作成

```bash
# プロジェクトルートから実行
# ./scripts/wt-new.sh <issue番号> <ブランチ名>
./scripts/wt-new.sh 130 feat/my-feature
```

`../keiba-db-gen2-wt/issue-130/` に worktree が作成され、以下が自動実行される:
- `composer install`（使い捨てコンテナ経由）
- `pnpm install`
- `.env` のコピーとポート・DB 名の自動割り当て

### worktree 内での開発

```bash
cd ../keiba-db-gen2-wt/issue-130/source

# コンテナ起動
./vendor/bin/sail up -d

# マイグレーション
./vendor/bin/sail artisan migrate

# テスト（worktree 独立の DB を使用）
DB_DATABASE=testing_wt1 ./vendor/bin/sail artisan test
```

### worktree 削除

```bash
./scripts/wt-rm.sh 130
```

### ポート割り当て

| オフセット | APP_PORT | VITE_PORT | FORWARD_DB_PORT | DB_DATABASE |
|----------|----------|-----------|----------------|-------------|
| main     | 80       | 5173      | 3306           | （.env の値） |
| 1        | 8001     | 5174      | 3307           | keiba_wt1   |
| 2        | 8002     | 5175      | 3308           | keiba_wt2   |
