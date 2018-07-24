<?php

namespace messere\phpValueMask\Mask;

interface IMask
{
    /**
     * apply filtering magic to $value and return filtered array
     * @param array|\stdClass $value
     * @return array
     */
    public function apply($value): array;

    /**
     * for nesting masks in masks, in tree-like structure
     * @param IMask $child
     */
    public function addChild(IMask $child): void;

    /**
     * check if mask matches $key
     * @param string $key
     * @return bool
     */
    public function match(string $key): bool;
}
