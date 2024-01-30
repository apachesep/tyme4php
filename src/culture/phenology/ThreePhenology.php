<?php

namespace com\tyme\culture\phenology;


use com\tyme\LoopTyme;

/**
 * 三候
 * @author 6tail
 * @package com\tyme\culture\phenology
 */
class ThreePhenology extends LoopTyme
{
    static array $NAMES = ['初候', '二候', '三候'];

    protected function __construct(int $index = null, string $name = null)
    {
        if ($index !== null) {
            parent::__construct(self::$NAMES, $index);
        } else if ($name !== null) {
            parent::__construct(self::$NAMES, null, $name);
        }
    }

    static function fromIndex(int $index): static
    {
        return new static($index);
    }

    static function fromName(string $name): static
    {
        return new static(null, $name);
    }

    function next(int $n): static
    {
        return self::fromIndex($this->nextIndex($n));
    }

}
