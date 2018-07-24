<?php

namespace messere\phpValueMask\Mask;

/**
 * Mask that will match any element, equivalent of wildcard (*) in filter definition
 */
class MaskAny extends Mask
{

    public function __construct()
    {
        parent::__construct(null, 1);
    }

    public function match(string $key): bool
    {
        return true;
    }
}
