# フロントエンド命名規則

## ファイル・ディレクトリ

| 対象 | 規則 | 例 |
|------|------|----|
| コンポーネントディレクトリ | PascalCase | `RaceCard/`, `BalanceForm/` |
| コンポーネントファイル | `index.tsx` | `RaceCard/index.tsx` |
| Storybookファイル | `ComponentName.stories.tsx` | `RaceCard/RaceCard.stories.tsx` |
| カスタムフック | camelCase + `use` プレフィックス | `useRaceCard.ts`, `useBalanceForm.ts` |
| ユーティリティ関数ファイル | camelCase | `date.ts`, `format.ts` |
| 型定義ファイル | camelCase | `race.ts`, `balance.ts` |
| ページコンポーネント | lowercase（URL構造に合わせる） + `index.tsx` | `pages/races/index.tsx` |

## コンポーネント

| 対象 | 規則 | 例 |
|------|------|----|
| コンポーネント名 | PascalCase | `RaceCard`, `BalanceForm` |
| Props型 | `ComponentName + Props` | `RaceCardProps`, `BalanceFormProps` |

## 変数・関数

| 対象 | 規則 | 例 |
|------|------|----|
| 変数名 | camelCase | `raceList`, `totalBalance` |
| 関数名 | camelCase | `formatCurrency`, `calcBalance` |
| 定数（モジュールスコープ） | UPPER_SNAKE_CASE | `MAX_RACE_COUNT` |
| カスタムフック | `use` + PascalCase | `useRaceList`, `useBalanceInput` |
| イベントハンドラ | `handle` + PascalCase | `handleSubmit`, `handleRaceSelect` |
| 関数定義 | アロー関数を使用 | `const foo = () => {}` |

## 型・インターフェース

| 対象 | 規則 | 例 |
|------|------|----|
| 型エイリアス | PascalCase | `Race`, `BalanceRecord` |
| インターフェース | PascalCase | `RaceResult`, `UserProfile` |
| Enum | PascalCase | `RaceStatus` |

## CSS（Tailwind）

クラス名は Tailwind のユーティリティクラスを使用する。カスタムクラスは原則設けない。  
クラスの整列順序は Biome の `organizeImports` に準拠する。
