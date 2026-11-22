# API一覧

| メソッド | パス | 概要 | 認証 |
|---|---|---|---|
| GET | `/api/races/{uid}/mark-columns` | ログインユーザーが所有する印列の一覧を取得する。 | 必要 |
| POST | `/api/races/{uid}/mark-columns` | 他人の印列を追加する（label を指定）。 | 必要 |
| PATCH | `/api/races/{uid}/mark-columns/{id}` | 他人の印列のラベルを更新する。 | 必要 |
| DELETE | `/api/races/{uid}/mark-columns/{id}` | 他人の印列を削除する（自分の印列は削除不可）。 | 必要 |
| PUT | `/api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/mark` | 印を設定または解除する（mark_value 空文字列で解除）。自動保存用。 | 必要 |
| PUT | `/api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo` | 印メモを作成または更新する（他人列のみ、自分列は422）。 | 必要 |
| DELETE | `/api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo` | 印メモを削除する（他人列のみ、自分列は422）。 | 必要 |
| POST | `/api/races/{uid}/nominations` | 登録馬（出走確定前の馬）を追加する（horse_id または horse_name。馬名一致がなければ生年なしで新規作成）。 | 必要 |
| DELETE | `/api/races/{uid}/nominations/{nomination_id}` | 登録馬を削除する（チェック／消しも削除、メモは残る）。 | 必要 |
| PUT | `/api/races/{uid}/nominations/{nomination_id}/mark` | 登録馬のチェック／消しを設定または解除する（status 空文字列で解除）。 | 必要 |
| GET | `/api/horses/search` | 馬名の部分一致で競走馬の候補を最大10件取得する。 | 必要 |
| GET | `/api/horses/{horse}/notes` | 認証ユーザーが所有する当該馬の全メモ（レース紐づきあり・なし含む）を取得する。 | 必要 |
| POST | `/api/horses/{horse}/notes` | 競走馬に対するメモを作成する（race_id は任意、null可）。 | 必要 |
| PUT | `/api/horse-notes/{note}` | メモ本文を更新する（content のみ更新可能）。 | 必要 |
| DELETE | `/api/horse-notes/{note}` | メモを物理削除する（自分のメモのみ削除可能、他ユーザー所有のメモは 403）。 | 必要 |
| PUT | `/api/trainers/{trainer}/note` | 認証ユーザーの調教師メモを作成または更新する（ユーザー × 調教師で 1 件）。 | 必要 |
| DELETE | `/api/trainers/{trainer}/note` | 認証ユーザーの調教師メモを削除する（存在しない場合も 204）。 | 必要 |
