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
        bigint user_id FK
        bigint venue_id FK "nullable"
        date race_date "nullable"
        tinyint race_number "1〜12 (nullable)"
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
        timestamp created_at
        timestamp updated_at
    }

    users ||--o{ races : "has"
    users ||--o{ ticket_purchases : "has"
    venues ||--o{ races : "has"
    races ||--o{ ticket_purchases : "has"
    ticket_types ||--o{ ticket_purchases : "has"
    buy_types ||--o{ ticket_purchases : "has"
```

## selectionsカラムのJSONフォーマット

| 買い方 | フォーマット | 例 |
|---|---|---|
| single / box（①複数頭選択） | `{"horses": [馬番...]}` | `{"horses": [1, 3, 5]}` |
| nagashi（②軸1頭+相手複数） | `{"axis": [馬番], "others": [馬番...]}` | `{"axis": [3], "others": [1, 5, 7]}` |
| nagashi（③軸2頭+相手複数） | `{"axis": [馬番, 馬番], "others": [馬番...]}` | `{"axis": [3, 5], "others": [1, 7]}` |
| formation（④着順別複数頭選択） | `{"columns": [[1着...], [2着...], [3着...]]}` | `{"columns": [[1,2],[3,4],[5,6,7]]}` |
