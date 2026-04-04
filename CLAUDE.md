# Claude Code
## ルール
- コミットする際には頭にissue番号を付ける
  - 例）issue番号が7だったら「#7 xxx」とコミットする

## 開発ワークフロー
新機能・改修を実装する際は以下の順序で進める:
1. `/dev-requirements <issue番号>` — 要件定義
2. `/dev-impl-prep <issue番号>` — 実装準備（テスト設計）
3. `/dev-impl <issue番号>` — 実装

各フェーズの完了前に次のフェーズへ進まない。