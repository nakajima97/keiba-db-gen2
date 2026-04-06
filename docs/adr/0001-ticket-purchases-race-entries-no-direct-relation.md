# ADR-0001: 購入馬券テーブルと出走馬テーブルの直接リレーションを持たない

## ステータス

承認済み

## コンテキスト

`ticket_purchases` テーブルの `selections` カラムはJSON形式で馬番号を管理している。

```json
// box/single
{"horses": [1, 3, 5]}
// nagashi
{"axis": [3], "others": [1, 5, 7]}
// formation
{"columns": [[1, 2], [3, 4], [5, 6, 7]]}
```

競走馬情報（`horses`）・出走馬情報（`race_entries`）テーブルを追加するにあたり、
`ticket_purchases` から `race_entries` への直接のFKリレーションを持つかどうかを検討した。

## 決定

直接リレーションは持たない。

## 理由

- `selections` のJSON構造が馬券種別（box/nagashi/formation）によって異なるため、正規化すると複雑な中間テーブルが必要になる
- 振り返り時に馬の情報が必要な場合は `race_id + horse_number` で `race_entries` と結合できる
- 馬に対するメモは `horse_memos` テーブルで管理するため、購入馬券経由で馬情報を参照する必要がない

## 影響

- 「特定の馬が絡む購入馬券一覧」のようなクエリは `selections` JSONの検索が必要になる
- 将来的にそのユースケースが重要になった場合はリレーションの追加を再検討する
