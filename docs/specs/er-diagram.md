# ER図

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email
        string password
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    venues {
        bigint id PK
        string name "東京/中山/阪神/京都/新潟/福島/小倉/函館/札幌/中京"
        timestamp created_at
        timestamp updated_at
    }

    races {
        bigint id PK
        string uid UK "URL用nanoid"
        bigint venue_id FK
        date race_date
        tinyint race_number "1〜12"
        string race_name "nullable"
        timestamp created_at
        timestamp updated_at
    }

    ticket_types {
        bigint id PK
        string name "tansho/fukusho/wakuren/umaren/umatan/wide/sanrenpuku/sanrentan"
        string label "単勝/複勝/枠連/馬連/馬単/ワイド/三連複/三連単"
        tinyint sort_order
        timestamp created_at
        timestamp updated_at
    }

    buy_types {
        bigint id PK
        string name "single/nagashi/box/formation"
        string label "通常/流し/ボックス/フォーメーション"
        tinyint sort_order
        timestamp created_at
        timestamp updated_at
    }

    ticket_purchases {
        bigint id PK
        bigint user_id FK
        bigint race_id FK "nullable"
        bigint ticket_type_id FK
        bigint buy_type_id FK
        json selections "馬番選択情報"
        int amount "購入金額（円）nullable"
        int payout_amount "払い戻し金額（円）nullable"
        timestamp created_at
        timestamp updated_at
    }

    race_payouts {
        bigint id PK
        bigint race_id FK
        bigint ticket_type_id FK
        int payout_amount "払い戻し金額（円）"
        tinyint popularity "人気順位"
        timestamp created_at
        timestamp updated_at
    }

    race_payout_horses {
        bigint id PK
        bigint race_payout_id FK
        tinyint horse_number "馬番（枠連は枠番）"
        tinyint sort_order "馬単・三連単は着順、それ以外は昇順"
        timestamp created_at
        timestamp updated_at
    }

    users ||--o{ ticket_purchases : "has"
    venues ||--o{ races : "has"
    races ||--o{ ticket_purchases : "has"
    ticket_types ||--o{ ticket_purchases : "has"
    buy_types ||--o{ ticket_purchases : "has"
    races ||--o{ race_payouts : "has"
    ticket_types ||--o{ race_payouts : "classified by"
    race_payouts ||--|{ race_payout_horses : "has"

    horses {
        bigint id PK
        string name "競走馬名"
        smallint birth_year "生年"
        timestamp created_at
        timestamp updated_at
    }

    jockeys {
        bigint id PK
        string name "騎手名"
        timestamp created_at
        timestamp updated_at
    }

    race_entries {
        bigint id PK
        bigint race_id FK
        bigint horse_id FK
        bigint jockey_id FK
        tinyint frame_number "枠番 1〜8"
        tinyint horse_number "馬番 1〜18"
        decimal weight "負担重量(kg) 4,1"
        smallint horse_weight "馬体重(kg) nullable"
        timestamp created_at
        timestamp updated_at
    }

    races ||--o{ race_entries : "has"
    horses ||--o{ race_entries : "participates in"
    jockeys ||--o{ race_entries : "rides in"

    race_result_horses {
        bigint id PK
        bigint race_id FK
        bigint horse_id FK "nullable"
        bigint jockey_id FK "nullable"
        tinyint finishing_order "着順"
        tinyint frame_number "枠番"
        tinyint horse_number "馬番"
        string horse_name "馬名"
        string sex_age "性齢"
        decimal weight "負担重量(kg)"
        string jockey_name "騎手名"
        string race_time "タイム"
        string time_difference "タイム差 nullable"
        string corner_order "コーナー順位 nullable"
        decimal estimated_pace "推定ペース nullable"
        smallint horse_weight "馬体重(kg) nullable"
        smallint horse_weight_change "馬体重増減(kg) nullable"
        string trainer_name "調教師名"
        tinyint popularity "人気順位"
        timestamp created_at
        timestamp updated_at
    }

    races ||--o{ race_result_horses : "has"
    horses ||--o{ race_result_horses : "has"
    jockeys ||--o{ race_result_horses : "has"
```

## selectionsカラムのJSONフォーマット

| 買い方 | フォーマット | 例 |
|---|---|---|
| single / box（①複数頭選択） | `{"horses": [馬番...]}` | `{"horses": [1, 3, 5]}` |
| nagashi（②軸1頭+相手複数） | `{"axis": [馬番], "others": [馬番...]}` | `{"axis": [3], "others": [1, 5, 7]}` |
| nagashi（③軸2頭+相手複数） | `{"axis": [馬番, 馬番], "others": [馬番...]}` | `{"axis": [3, 5], "others": [1, 7]}` |
| formation（④着順別複数頭選択） | `{"columns": [[1着...], [2着...], [3着...]]}` | `{"columns": [[1,2],[3,4],[5,6,7]]}` |
