# プロジェクト構造と設計方針（バックエンド）

## ディレクトリ構成

```
.
├── app/
│   ├── Console/
│   │   └── Commands/           # Artisan コマンド
│   ├── Exceptions/             # ドメイン固有の例外
│   │   └── FeatureName/        # 例: Post/, User/ など機能ごとに分類
│   ├── Http/
│   │   ├── Controllers/        # HTTP オーケストレーションのみ担当（Inertia::render を返す）
│   │   ├── Middleware/         # ミドルウェア
│   │   └── Requests/           # フォームバリデーション・認可（FormRequest）
│   │       └── FeatureName/    # 例: Post/, User/
│   ├── Models/                 # Eloquent モデル（DBテーブルの操作を定義）
│   ├── Providers/              # サービスプロバイダ
│   └── UseCases/               # ビジネスロジック（1アクション1クラス）
│       └── FeatureName/        # 例: Post/, User/
│           ├── StoreAction.php
│           ├── UpdateAction.php
│           └── DestroyAction.php
├── routes/
│   └── web.php                 # ルート定義（Inertia.js のページコンポーネントと対応）
├── resources/
│   ├── js/                     # フロントエンド（React + Inertia.js）
│   └── views/
│       └── app.blade.php       # Inertia.js エントリーポイント
└── database/
    ├── migrations/             # マイグレーション
    ├── seeders/                # シーダー
    └── factories/              # ファクトリ
```

## 各レイヤーの役割

| レイヤー | 役割 |
|---------|------|
| `Http/Controllers` | HTTPリクエストの受け取りと `Inertia::render()` の呼び出しのみ。ビジネスロジックは持たない |
| `Http/Requests` | バリデーションと認可（`authorize()`）を一本化 |
| `UseCases` | ビジネスロジックとドメイン検証をカプセル化。1アクション1クラス |
| `Models` | Eloquent によるDBアクセス。Repository パターンは導入しない |
| `Exceptions` | HTTP非依存のドメイン例外。コントローラ側で HTTP レスポンスに変換する |

## 設計方針

### UseCase の単一責務

1クラス = 1アクション。変更理由を1つに限定する。

```php
// app/UseCases/Post/StoreAction.php
class StoreAction
{
    public function execute(StorePostData $data): Post
    {
        // ビジネスロジックはここに集約
    }
}
```

### Controller は薄く保つ

Controller の責務は「UseCase を呼び出して `Inertia::render()` で返すだけ」。

```php
class PostController extends Controller
{
    public function store(StorePostRequest $request, StoreAction $action)
    {
        $action->execute($request->validated());
        return redirect()->route('posts.index');
    }

    public function index()
    {
        return Inertia::render('Posts/Index', [
            'posts' => Post::all(),
        ]);
    }
}
```

### Repository パターンは導入しない

ActiveRecord 指向の Eloquent と Repository パターンは相性が悪い。
UseCase 内で直接 Eloquent Builder を使用する。

### テスト方針

ユニットテストより `RefreshDatabase` を使った機能テスト（Feature Test）を優先する。
Eloquent を直接使う設計のため、DBを介さないモックテストは現実的でない。

### ルーティング

ルートは `routes/web.php` で一元管理する。
フロントエンドの `resources/js/pages/` のコンポーネントと対応させる。

```php
// routes/web.php
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
// → resources/js/pages/Posts/Index.tsx を Inertia::render で返す
```
