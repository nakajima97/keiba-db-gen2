# API一覧

| メソッド | パス | 概要 | 認証 |
|---|---|---|---|
| GET | `/api/races/{uid}/mark-columns` | ログインユーザーが所有する印列の一覧を取得する。 | 必要 |
| POST | `/api/races/{uid}/mark-columns` | 他人の印列を追加する（label を指定）。 | 必要 |
| PATCH | `/api/races/{uid}/mark-columns/{id}` | 他人の印列のラベルを更新する。 | 必要 |
| DELETE | `/api/races/{uid}/mark-columns/{id}` | 他人の印列を削除する（自分の印列は削除不可）。 | 必要 |
| PUT | `/api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/mark` | 印を設定または解除する（mark_value 空文字列で解除）。自動保存用。 | 必要 |
