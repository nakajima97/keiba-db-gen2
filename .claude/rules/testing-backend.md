---
description: バックエンドのテスト方針（Laravel/Pest）
paths: ["source/tests/Feature/**/*.php"]
alwaysApply: false
---

# バックエンドテスト方針

## テストの対象

APIエンドポイントを実装したら、そのエンドポイントに対して統合テストを書く（`tests/Feature/`）。

現状のアーキテクチャでは依存のない単体テスト対象が存在しないため、単体テストは書かない。

## テストを書くときの注意点

- カバレッジが最も高いハッピーパス1つだけを対象にする
- 目的はシステム全体の接続確認に限定する

## モックの境界

| 対象 | 方針 |
|------|------|
| DB | モックしない。`RefreshDatabase` を使って実DBで検証する |
| 外部サービス（メール等） | Laravelの Fake 機能（`Mail::fake()` 等）でモックする |

GitHub Actions では MySQL サービスコンテナが起動済みのため、`RefreshDatabase` はCIでそのまま動作する。
