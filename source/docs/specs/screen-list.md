# 画面一覧（レース結果関連）

| 画面名 | パス | HTTPメソッド | コントローラ#メソッド | 説明 |
|--------|------|------------|----------------------|------|
| レース結果入力画面 | `/races/{uid}/result/new` | GET | `RaceResultController#create` | 着順・払戻を入力するフォーム。`has_existing_result=true` の場合は入力欄がdisabled |
| レース結果確認画面 | `/races/{uid}/result/edit` | GET | `RaceResultController#edit` | 登録済みの着順・払戻を表示する。削除ボタンを表示し、押下で確認モーダルを開く |
