---
description: フロントエンドのテスト方針（React/Vitest）
paths: ["source/resources/js/**/*.unit.test.ts", "source/resources/js/**/*.unit.test.tsx", "source/resources/js/**/*.int.test.ts", "source/resources/js/**/*.int.test.tsx"]
---

# フロントエンドテスト方針

## テストの種類と使い分け

単体テストと統合テストを明確に分ける。

| 種類 | ファイル命名 | モック |
|------|------------|--------|
| 単体テスト | `*.unit.test.ts` | 使わない |
| 統合テスト | `*.int.test.ts` | 使う |

カバレッジは単体テストのみ計測する。

```sh
vitest run --include "**/*.unit.test.ts" --coverage
```

## 単体テストの対象

- **Presentationalコンポーネント**（P/Cパターンの P）
- **カスタムhooksから切り出した純粋関数**

### カスタムhooksの設計方針

カスタムhooksはオーケストレーション層として機能させる。

- あるユースケースを実現する純粋関数を切り出して呼び出す
- stateが必要な場合のみhooks内でstateを管理する
- useMemoなど適切なパフォーマンス最適化を行う

### 単体テスト対象の関数に課す制約

- 純粋関数にする（参照透過性を保つ）
- stateを持たない

参考: https://ja.react.dev/learn/keeping-components-pure

## 統合テストの対象

- **Containerコンポーネント**（P/Cパターンの C）
- **カスタムhooks**
- **ページコンポーネント**

## AAAパターン

テストコードはArrange（準備）/ Act（実行）/ Assert（検証）の3フェーズに分けて記述し、各フェーズに以下のコメントを付ける。

```ts
it("テスト名", () => {
    // Arrange
    const props = { ... };

    // Act
    render(<Component {...props} />);

    // Assert
    expect(screen.getByText("...")).toBeInTheDocument();
});
```

ArrangeのないテストはActとAssertのみコメントを付ける。

## テストを書くときの注意点

### 単体テストの場合

- 振る舞いだけを検証する
- テスト対象を呼ぶ行は1行で済むようにする
- カバレッジを指標にする際はif文を意識しつつも、あくまで入力値・出力値のみを意識する
  - 実装を把握したうえでブラックボックステストを書くイメージ

### 統合テストの場合

- カバレッジが最も高いハッピーパス1つだけを対象にする
- 目的はシステム全体の接続確認に限定する
