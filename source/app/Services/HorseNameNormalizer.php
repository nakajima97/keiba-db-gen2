<?php

namespace App\Services;

/**
 * 馬名文字列を正規化するユーティリティ。
 *
 * JRA 公式サイトからコピペしたテキストには、外国産馬や地方競馬所属馬を示す注記が
 * 馬名と連結した形（例: `マルガイユウファラオ`）で含まれることがある。
 * この注記を除去し、`horses.name` には馬名本体だけが流れるようにする。
 */
class HorseNameNormalizer
{
    /**
     * JRA 着順テキスト・出馬表テキストに現れる馬名の注記 prefix。
     *
     * 現在の JRA で出馬表にテキスト化されることが確認できているもののみを対象とする。
     * 廃止された prefix（マルフ・マルイチ など）や裏付けが取れていないものは含めない。
     *
     * @var array<int, string>
     */
    private const JRA_ANNOTATION_PREFIXES = [
        'マルガイ', // ○外: 外国産馬
        'カクガイ', // □外: 外国馬（海外調教馬）
        'マルチ',   // ○地: 地方競馬所属馬
    ];

    /**
     * 馬名から JRA 注記 prefix を除去する。
     *
     * 先頭が登録済み prefix のいずれかと一致する場合に限り、最長一致した prefix を
     * 1 回だけ取り除いて返す。除去後に空文字になる場合（馬名が prefix のみで構成）は
     * 元の文字列をそのまま返し、誤除去を避ける。
     */
    public static function stripJraAnnotationPrefix(string $name): string
    {
        $matchedPrefix = null;
        foreach (self::JRA_ANNOTATION_PREFIXES as $prefix) {
            if (! str_starts_with($name, $prefix)) {
                continue;
            }
            if ($matchedPrefix === null || mb_strlen($prefix) > mb_strlen($matchedPrefix)) {
                $matchedPrefix = $prefix;
            }
        }

        if ($matchedPrefix === null) {
            return $name;
        }

        $stripped = mb_substr($name, mb_strlen($matchedPrefix));
        if ($stripped === '') {
            return $name;
        }

        return $stripped;
    }
}
