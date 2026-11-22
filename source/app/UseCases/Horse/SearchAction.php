<?php

namespace App\UseCases\Horse;

use App\Models\Horse;

/**
 * 馬名入力欄の候補表示用に、馬名の部分一致で競走馬を検索する。
 *
 * keyword 未指定（空文字・空白のみ含む）時は空配列を返す。
 */
class SearchAction
{
    /** 候補の最大件数 */
    public const LIMIT = 10;

    /**
     * @return list<array{id: int, name: string, birth_year: int|null}>
     */
    public function execute(?string $keyword): array
    {
        $keyword = $keyword !== null ? trim($keyword) : '';
        if ($keyword === '') {
            return [];
        }

        return Horse::query()
            ->whereRaw("name LIKE ? ESCAPE '!'", ['%'.self::escapeLike($keyword).'%'])
            ->orderBy('name')
            ->orderBy('id')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'birth_year'])
            ->map(fn (Horse $horse): array => [
                'id' => (int) $horse->id,
                'name' => $horse->name,
                'birth_year' => $horse->birth_year !== null ? (int) $horse->birth_year : null,
            ])
            ->values()
            ->all();
    }

    /**
     * LIKE のワイルドカード（% と _）を文字として扱うためにエスケープする。
     * DB ごとのデフォルトエスケープ文字の差異を避けるため ESCAPE '!' を明示する（Horse\IndexAction と同じ方式）。
     */
    private static function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}
