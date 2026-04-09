<?php

namespace App\Support;

final class NanoId
{
    private const ALPHABET =
        '_-0123456789abcdefghijklmnopqrstuvwxyz'
        .'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function generate(int $size = 21): string
    {
        $alphabet = self::ALPHABET;
        $mask = (2 << (int) log(strlen($alphabet) - 1, 2)) - 1;
        $step = (int) ceil(1.6 * $mask * $size / strlen($alphabet));

        $id = '';
        while (strlen($id) < $size) {
            $bytes = random_bytes($step);
            for ($i = 0; $i < $step && strlen($id) < $size; $i++) {
                $byte = ord($bytes[$i]) & $mask;
                if (isset($alphabet[$byte])) {
                    $id .= $alphabet[$byte];
                }
            }
        }

        return $id;
    }
}
