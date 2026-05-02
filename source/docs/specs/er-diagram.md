# ER図（レース結果関連）

```mermaid
erDiagram
    races {
        bigint id PK
        string uid
        string venue_name
        date race_date
        int race_number
    }

    race_result_horses {
        bigint id PK
        bigint race_id FK
        int finishing_order
        int frame_number
        int horse_number
        string horse_name
        string jockey_name
        string race_time
    }

    race_payouts {
        bigint id PK
        bigint race_id FK
        string ticket_type_name
        int payout_amount
        int popularity
    }

    race_payout_horses {
        bigint id PK
        bigint race_payout_id FK
        int horse_number
        int sort_order
    }

    ticket_purchases {
        bigint id PK
        bigint race_id FK
        string ticket_type_name
        int unit_stake
        int|null payout_amount
    }

    races ||--o{ race_result_horses : "1レースに複数の着順馬"
    races ||--o{ race_payouts : "1レースに複数の払戻"
    race_payouts ||--o{ race_payout_horses : "1払戻に複数の対象馬番"
    races ||--o{ ticket_purchases : "1レースに複数の馬券購入"
```

## 削除時の連鎖

`race_result_horses` および `race_payouts` は `race_id` に `cascadeOnDelete` が設定されているため、`races` 削除時に自動削除される。

`race_payout_horses` は `race_payout_id` に `cascadeOnDelete` が設定されているため、`race_payouts` 削除時に自動削除される。

`ticket_purchases.payout_amount` は削除制約なし。レース結果削除時にアプリケーション側で `NULL` にリセットする。
