<?php

namespace App\Concerns;

use App\Support\NanoId;
use Illuminate\Database\QueryException;

/**
 * NanoId 由来の uid カラムを採番する Eloquent モデル向け Trait。
 *
 * - creating フックで uid 未設定なら採番する
 * - save() 時に uid カラムの UNIQUE 衝突 (SQLSTATE 23000) が起きたら
 *   再採番して最大 self::$uidMaxRetries 回までリトライする
 * - 上限超過時は最後の QueryException をそのまま throw する
 * - テスト時は fakeUidQueue() で採番値を差し替えられる
 */
trait HasNanoIdUid
{
    protected static string $uidColumn = 'uid';

    protected static int $uidMaxRetries = 3;

    /** @var array<int, string> */
    protected static array $fakeUidQueue = [];

    public static function bootHasNanoIdUid(): void
    {
        static::creating(function (self $model): void {
            $column = static::$uidColumn;
            if (empty($model->{$column})) {
                $model->{$column} = static::generateUid();
            }
        });
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            return parent::save($options);
        }

        $attempts = 0;
        while (true) {
            try {
                return parent::save($options);
            } catch (QueryException $e) {
                if (! static::isUidUniqueViolation($e) || $attempts >= static::$uidMaxRetries) {
                    throw $e;
                }
                $attempts++;
                $this->{static::$uidColumn} = static::generateUid();
            }
        }
    }

    protected static function isUidUniqueViolation(QueryException $e): bool
    {
        if ((string) $e->getCode() !== '23000') {
            return false;
        }
        $col = static::$uidColumn;
        $msg = $e->getMessage();

        // MySQL: "for key 'horses.horses_uid_unique'"
        // SQLite: "UNIQUE constraint failed: horses.uid"
        return str_contains($msg, ".{$col}")
            || str_contains($msg, "_{$col}_unique")
            || str_contains($msg, "'{$col}'");
    }

    protected static function generateUid(): string
    {
        if (! empty(static::$fakeUidQueue)) {
            return array_shift(static::$fakeUidQueue);
        }

        return NanoId::generate();
    }

    /**
     * @param  array<int, string>  $ids
     */
    public static function fakeUidQueue(array $ids): void
    {
        static::$fakeUidQueue = $ids;
    }

    public static function clearUidGeneratorState(): void
    {
        static::$fakeUidQueue = [];
    }
}
