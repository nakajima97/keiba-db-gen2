# API一覧

JSONレスポンスを返すREST APIの一覧。画面（Inertia）を返すルートは含まない。

| メソッド | パス | 操作 | コントローラ#メソッド | 説明 |
|---------|------|------|----------------------|------|
| POST | `/races/{uid}/result` | レース結果登録 | `RaceResultController#store` | 着順・払戻データを登録し、ticket_purchases.payout_amountを更新する |
| DELETE | `/races/{uid}/result` | レース結果削除 | `RaceResultController#destroy` | 着順・払戻データを削除し、ticket_purchases.payout_amountをNULLにリセットする |
