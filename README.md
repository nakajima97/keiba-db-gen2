# keiba-db-gen2
## 概要
個人利用目的の競馬DB

## 開発
開発フローは以下
https://github.com/nakajima97/ai-driven-development

## ドキュメント
docs配下を確認

## セットアップ
~~~bash
# 移動（のちに記載したコマンドの前提条件）
cd ./source

# 初回のみ: Docker イメージのビルド
./vendor/bin/sail build --no-cache

# コンテナ起動
./vendor/bin/sail up -d

# マイグレーション
./vendor/bin/sail artisan migrate

# シード
./vendor/bin/sail artisan db:seed

# フロント
pnpm dev

# 停止
./vendor/bin/sail down

# フロントリント
pnpm lint

# フロントフォーマット
pnpm format
~~~