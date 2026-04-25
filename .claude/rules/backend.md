---
description: バックエンドのルール（Laravel PHP）
paths: ["app/**/*.php", "database/**/*.php", "routes/**/*.php"]
alwaysApply: false
---

## ディレクトリ構成

バックエンドファイルを作成・配置する際は必ず [docs/architecture/backend/directory-structure.md](../../docs/architecture/backend/directory-structure.md) に従うこと。

## DB アクセス方法の使い分け

- **基本は Eloquent Model を使う。** 単純な ID 逆引き・単一テーブルへのアクセスは必ず `TicketType::where(...)` のように Eloquent Model で行う。
- **`DB::table()` / Query Builder は集計・JOIN クエリに限定する。** 複数テーブルをまたぐ集計や N+1 を避けたい JOIN クエリの場合のみ使用してよい。
- **Eloquent + 手動 JOIN（`Model::query()->join()`）は使わない。** JOIN を使うなら `DB::table()` で書く。Eloquent を使うなら JOIN を避けてリレーションで取得する。
