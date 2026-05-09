---
description: バックエンドのテスト方針（Laravel/Pest）
paths: ["source/tests/Feature/**/*.php"]
alwaysApply: false
---

# バックエンドテスト方針

## テストの対象

APIエンドポイントを実装したら、そのエンドポイントに対して統合テストを書く（`tests/Feature/`）。

現状のアーキテクチャでは依存のない単体テスト対象が存在しないため、単体テストは書かない。

### スキーマ変更のみの場合

APIエンドポイントの変更を伴わないスキーマ変更（型拡張・カラム追加など）で、変更意図がアプリケーションコード上の振る舞いとして観測できず将来の編集で退行しうるものについては、その意図を退行から守る境界値テストを1つだけ書く。例: 型を `unsignedTinyInteger` から `unsignedSmallInteger` に拡張したなら、新たに保存可能になった最大値（65535）を保存できることを1ケースだけ検証する。

## テストを書くときの注意点

- 各振る舞いごとにカバレッジが最も高いハッピーパス1つだけを対象にする
- 目的はエンドポイントの振る舞いを保証することに限定する

## AAAパターン

テストコードはArrange（準備）/ Act（実行）/ Assert（検証）の3フェーズに分けて記述し、各フェーズに以下のコメントを付ける。

```php
test('テスト名', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('dashboard'));

    // Assert
    $response->assertOk();
});
```

Arrangeのないテスト（画面表示確認など）はActとAssertのみコメントを付ける。

## モックの境界

| 対象 | 方針 |
|------|------|
| DB | モックしない。`RefreshDatabase` を使って実DBで検証する |
| 外部サービス（メール等） | Laravelの Fake 機能（`Mail::fake()` 等）でモックする |

GitHub Actions では MySQL サービスコンテナが起動済みのため、`RefreshDatabase` はCIでそのまま動作する。

## テスト説明文の言語

`test()` / `it()` / `describe()` の説明文および `->with([...])` のキーは日本語で記述する。固有名詞・カラム名・HTTPステータス・`null` 等の技術用語はそのまま英数字を残す。
