# 画面遷移図

```mermaid
flowchart TD
    Login[ログイン画面] -->|認証成功| Dashboard[ダッシュボード]
    Dashboard -->|馬券登録ボタン| TicketsNew[馬券登録画面\n/tickets/new]
    TicketsNew -->|登録ボタン押下| TicketsNew
    Dashboard -->|購入馬券一覧ボタン| TicketsIndex[購入馬券一覧画面\n/tickets]
    TicketsIndex -->|馬券を登録するボタン| TicketsNew
```
