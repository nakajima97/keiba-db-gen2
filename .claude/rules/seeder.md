---
paths: ["source/database/seeders/**/*.php"]
---

# シーダーの実装方針

## タイムスタンプ

`DB::table()->insert()` を使う場合、Eloquentがバイパスされるため `created_at` / `updated_at` は自動設定されない。
必ず `now()` を使って手動で設定すること。

```php
$now = now();
$rows = [
    ['name' => 'foo', 'created_at' => $now, 'updated_at' => $now],
];
DB::table('example')->insert($rows);
```

`$now` は一度だけ呼び出して全レコードで使い回すこと。
