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

## 契約テスト（OpenAPI ↔ API レスポンスの突合）

API エンドポイントを新規追加、またはリクエスト/レスポンス構造を変更した場合は、`tests/Feature/Contract/` に Spectator を使った契約テストを1本追加する（happy-path のみ）。`docs/specs/openapi.yaml` の更新と必ずセットで行う。

- 配置: `tests/Feature/Contract/<Resource>ContractTest.php`
- 1ファイル1リソース、1エンドポイントにつき1テストで happy-path のみカバーする
- 雛形:

```php
use Spectator\Spectator;

beforeEach(function () {
    Spectator::using('openapi.yaml');
});

test('contract: <operationId> matches OpenAPI spec', function () {
    // Arrange
    $user = User::factory()->create();
    // ... エンドポイントが要求する最小構成のデータを用意

    // Act
    $response = $this->actingAs($user)->getJson('/api/...');

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});
```

- 既存例: `tests/Feature/Contract/RaceMarkColumnContractTest.php`
- 異常系（401/403/404/422）の契約検証はスコープ外。必要になったタイミングで追加する
