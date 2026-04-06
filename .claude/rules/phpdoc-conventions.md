---
description: PHPDocの書き方ルール（PHP 8.3+）
paths: ["source/app/**/*.php"]
alwaysApply: false
---

# PHPDoc 規約（PHP 8.3+）

## 基本方針

PHP 8.3 の型ヒントで表現できる情報は PHPDoc に重複させない。
PHPDoc は型ヒントに載せられない情報（配列シェイプ、ジェネリクス、意図の説明）のみを追加する。

## 必須ケース

| 対象 | 書き方 |
|---|---|
| UseCase / Action クラスのクラスブロック | クラスの責務と処理の概要を記述する |
| UseCase の `execute()` で `array` 型引数を受け取る場合 | `@param array{key: type, ...} $data` で構造を明示する |
| Eloquent リレーションメソッド | `@return HasMany<Race, $this>` 等のジェネリクスを付与する |
| 明示的に例外を throw するメソッド | `@throws` を記述する |

## 省略可能なケース

| 対象 | 理由 |
|---|---|
| `authorize(): bool` の `@return` | 型ヒントで自明 |
| コンストラクタプロパティプロモーション | 型ヒントで意図が明確 |
| 単純な CRUD コントローラーアクション | メソッド名と引数型から役割が分かる |
| getter / setter | 型ヒントが完全な情報を持つ |
