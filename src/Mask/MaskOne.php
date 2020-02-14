<?php

namespace messere\phpValueMask\Mask;

class MaskOne extends Mask
{
    private $key;

    public function __construct(string $key)
    {
        parent::__construct(1, 1);
        $this->key = $key;
    }

    public function match(string $key): bool
    {
        return $this->key === $key;
    }
}
