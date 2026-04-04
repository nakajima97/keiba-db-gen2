# システム全体構成

## 概要

競馬の収支記録を中心としたデータを管理するWebアプリケーション。

## 技術選定

| レイヤー | 技術 | バージョン |
|---------|------|-----------|
| バックエンド | Laravel | ^13.0 |
| フロントエンド | React | ^19.0 |
| SPA連携 | Inertia.js | ^3.0 |
| DB | MySQL | 8.4 |
| コンテナ | Laravel Sail (Docker) | - |
| CSSフレームワーク | Tailwind CSS | ^4.0 |
| UIコンポーネント | shadcn/ui (Radix UI) | - |
| 言語（フロント） | TypeScript | ^5.7 |
| Linter/Formatter（フロント） | Biome | ^1.9 |
| Linter（バック） | Laravel Pint | ^1.27 |
| テスト（バック） | Pest | ^4.4 |
| テスト（フロント） | Vitest | ^4.0 |

## システム構成

```
ブラウザ
  ↓ HTTP
Laravel (Sail コンテナ)
  ├── Inertia.js（サーバーサイドルーティング・データ受け渡し）
  └── React（クライアントサイドレンダリング）
  ↓
MySQL 8.4（Sail コンテナ）
```

外部サービス連携なし。

## レイヤー構成・責務分離

| レイヤー | 責務 |
|---------|------|
| `Http/Controllers` | HTTPリクエスト受け取りと `Inertia::render()` の呼び出しのみ |
| `Http/Requests` | バリデーションと認可 |
| `UseCases` | ビジネスロジック（1アクション1クラス） |
| `Models` | Eloquent によるDBアクセス |
| `resources/js/pages` | Inertia ページコンポーネント（ルートと1対1対応） |
| `resources/js/features` | 機能単位のコンポーネント・ロジック |
| `resources/js/components` | アプリ全体で共有する再利用コンポーネント |

## 認証・認可

Laravel Fortify を使用。認証状態は Inertia の shared data を通じてフロントに渡す。

## フロントエンド↔バックエンドのAPI連携方針

Inertia.js を通じてデータを受け渡す。REST API は原則設けず、フォーム送信・ページ遷移はすべて Inertia の `router` を使用する。
