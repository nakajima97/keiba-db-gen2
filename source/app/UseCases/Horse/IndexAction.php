<?php

namespace App\UseCases\Horse;

use App\Models\Horse;
use Illuminate\Support\Collection;

/**
 * 競走馬検索画面の表示用データを返す。
 *
 * keyword 指定時は馬名の部分一致で競走馬を検索し、未指定（空文字含む）時は horses を空配列とする。
 */
class IndexAction
{
    /** 検索結果の最大件数 */
    public const LIMIT = 50;

    /**
     * @return array{
     *     horses: Collection<int, array{uid: string, name: string, birth_year: int|null}>|array<int, mixed>,
     *     filters: array{keyword: string|null},
     * }
     */
    public function execute(?string $keyword): array
    {
        $keyword = $keyword !== null ? trim($keyword) : null;
        if ($keyword === '') {
            $keyword = null;
        }

        $horses = $keyword !== null ? Horse::query()
            ->whereRaw("name LIKE ? ESCAPE '!'", ['%'.self::escapeLike($keyword).'%'])
            ->orderBy('name')
            ->orderBy('id')
            ->limit(self::LIMIT)
            ->get(['uid', 'name', 'birth_year'])
            ->map(fn (Horse $horse) => [
                'uid' => $horse->uid,
                'name' => $horse->name,
                'birth_year' => $horse->birth_year !== null ? (int) $horse->birth_year : null,
            ]) : [];

        return [
            'horses' => $horses,
            'filters' => [
                'keyword' => $keyword,
            ],
        ];
    }

    /**
     * LIKE のワイルドカード（% と _）を文字として扱うためにエスケープする。
     *
     * エスケープ文字のデフォルトが DB ごとに異なる（MySQL はバックスラッシュ、SQLite はなし）ため、
     * ESCAPE '!' を明示して DB に依存しないようにしている。
     */
    private static function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}
