# Technical Environment Document
フロントエンド・バックエンド共通の技術環境・開発環境について説明したファイル
それぞれ固有のものは `docs` ディレクトリを参照

## 言語・ランタイム

| 項目 | バージョン | 採用理由 |
|------|-----------|---------|
| PHP | 8.3 | Laravel 13 の必須要件、かつレンタルサーバーで動作する |
| Node.js | LTS | フロントエンドビルド用（本番環境では実行しない） |

## パッケージマネージャー

| 対象 | ツール |
|------|--------|
| PHP | Composer |
| JavaScript | pnpm |

## フレームワーク・ライブラリ

| 項目 | バージョン | 用途 |
|------|-----------|------|
| Laravel | 13.x | バックエンドフレームワーク |
| Inertia.js | 3.x | サーバーサイドとフロントエンドの統合 |
| React | 19.x | フロントエンド UI |
| TypeScript | 5.x | フロントエンド型安全性 |
| Tailwind CSS | 4.x | スタイリング |
| shadcn/ui | 最新安定版 | UI コンポーネント（Radix UI ベース） |
| Laravel Fortify | 1.x | 認証バックエンド |

スターターキット: [laravel/react-starter-kit](https://laravel.com/starter-kits#react)

## データベース

| 項目 | バージョン |
|------|-----------|
| MySQL | 8.x |

## テスト

| 項目 | 内容 |
|------|------|
| テストフレームワーク | Pest |
| 対象 | バックエンドのビジネスロジック・API |

## デプロイ

| 項目 | 内容 |
|------|------|
| ホスティング | レンタルサーバー（PHP対応） |
| ビルドパイプライン | GitHub Actions で `pnpm run build` を実行し、生成した dist をサーバーにデプロイ |
| サーバー上での Node.js 実行 | 不可（ビルドは CI のみ） |

## 使用禁止ライブラリ

| ライブラリ | 理由 | 代替 |
|-----------|------|------|
| JRA-VAN SDK 等の有料データAPI | 費用が発生するため | 手入力・コピペ対応 |

## ディレクトリ構成

```
keiba-db/
├── docs/          # ドキュメント類
├── source/        # Laravelプロジェクト（アプリケーションコード）
└── CLAUDE.md
```

フロントエンドのディレクトリ設計（`source/resources/js/` 配下）は [docs/architecture/frontend/directory-structure.md](docs/architecture/frontend/directory-structure.md) を参照。

## ローカル開発環境

| 項目 | 内容 |
|------|------|
| 構築方法 | Laravel Sail（Docker） |
| 起動コマンド | `cd source && ./vendor/bin/sail up -d` |
| 前提条件 | Docker Desktop インストール済み |

## セキュリティ方針

| 項目 | 内容 |
|------|------|
| 認証 | Laravel Fortify（セッション認証） |
| 対象ユーザー | 個人利用のみ（シングルユーザー） |
| データ | 競馬記録データはリポジトリにコミットしない |
| CSRF | Laravel 標準の CSRF 保護を使用 |