<?php

namespace App\Exceptions\RaceResult;

class ParseException extends \InvalidArgumentException
{
    public function __construct(
        string $message,
        public readonly string $field,
    ) {
        parent::__construct($message);
    }
}
