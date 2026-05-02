<?php

namespace App\Exceptions\RaceResult;

/**
 * 削除対象のレース結果（race_result_horses / race_payouts）が存在しないことを表す例外。
 */
class NoResultToDestroyException extends \RuntimeException {}
