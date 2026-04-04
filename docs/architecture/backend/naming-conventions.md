# バックエンド命名規則

## ファイル・クラス

| 対象 | 規則 | 例 |
|------|------|----|
| Controller | `{リソース名}Controller` | `RaceController`, `BalanceController` |
| FormRequest | `{動詞}{リソース名}Request` | `StoreRaceRequest`, `UpdateBalanceRequest` |
| UseCase（Action） | `{動詞}Action`（リソース名はディレクトリで表現） | `StoreAction`, `DestroyAction` |
| Model | 単数形 PascalCase | `Race`, `BalanceRecord` |
| Exception | `{意味のある名前}Exception` | `RaceNotFoundException` |
| Migration | スネークケース（自動生成） | `2024_01_01_000000_create_races_table.php` |
| Seeder | `{リソース名}Seeder` | `RaceSeeder` |
| Factory | `{モデル名}Factory` | `RaceFactory` |

## メソッド

| 対象 | 規則 | 例 |
|------|------|----|
| 一般メソッド | camelCase | `calculateBalance()` |
| UseCase の実行メソッド | `execute()` で統一 | `execute(StoreRaceData $data)` |
| アクセサ（Eloquent） | `get{属性名}Attribute` | `getFormattedDateAttribute()` |
| スコープ（Eloquent） | `scope{名前}` | `scopeActive()` |

## 変数・プロパティ

| 対象 | 規則 | 例 |
|------|------|----|
| 変数名 | camelCase | `$raceList`, `$totalBalance` |
| プロパティ | camelCase | `$raceName`, `$betAmount` |
| 定数 | UPPER_SNAKE_CASE | `const MAX_BET_AMOUNT = 100000` |

## ルート名

`{リソース名（複数形）}.{アクション}` の形式。

```php
Route::get('/races', ...)->name('races.index');
Route::post('/races', ...)->name('races.store');
Route::get('/races/{race}', ...)->name('races.show');
Route::put('/races/{race}', ...)->name('races.update');
Route::delete('/races/{race}', ...)->name('races.destroy');
```

## DBカラム名

スネークケース。Laravel の規約に従う。

| 対象 | 規則 | 例 |
|------|------|----|
| 主キー | `id` | `id` |
| 外部キー | `{テーブル名単数形}_id` | `race_id` |
| タイムスタンプ | `created_at`, `updated_at` | - |
| フラグ | `is_{状態}` | `is_active`, `is_deleted` |

## UseCase の配置

機能単位でディレクトリを切る。1アクション1クラス。

```
app/UseCases/
└── Race/
    ├── StoreAction.php
    ├── UpdateAction.php
    └── DestroyAction.php
```
