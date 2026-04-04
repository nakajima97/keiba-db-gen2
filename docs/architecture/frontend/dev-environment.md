# フロントエンド開発環境

## 前提

バックエンドの開発環境（Laravel Sail）が起動済みであること。  
→ [バックエンド開発環境](../backend/dev-environment.md) を参照。

## セットアップ

`source/` ディレクトリで実行する。

```bash
cd source

# 依存パッケージのインストール
pnpm install
```

## 開発サーバー起動

Sail 経由で Laravel と Vite を同時に起動する。

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

または `composer run dev` でまとめて起動できる（concurrently でサーバー・キュー・ログ・Viteを並列起動）。

## ビルド

```bash
pnpm run build
```

## コード品質

| コマンド | 内容 |
|---------|------|
| `pnpm run lint` | Biome でlint（自動修正あり） |
| `pnpm run lint:check` | Biome でlint（チェックのみ） |
| `pnpm run format` | Biome でフォーマット（自動修正あり） |
| `pnpm run format:check` | Biome でフォーマット（チェックのみ） |
| `pnpm run tc` | TypeScript 型チェック |

## テスト

```bash
pnpm run test
```

Vitest を使用。設定ファイル: `source/vitest.config.ts`

## 主要ツール

| ツール | 用途 |
|--------|------|
| Vite | バンドラー・開発サーバー |
| TypeScript | 型安全な開発 |
| Biome | Linter / Formatter（ESLint + Prettier の代替） |
| Tailwind CSS v4 | スタイリング |
| shadcn/ui | UIコンポーネントライブラリ |
| Vitest | ユニットテスト |
