<?php

namespace messere\phpValueMask\Mask;

/**
 * Mask that will apply all children masks to value, then
 * gather and merge results to produce filtered value
 */
class MaskArray extends Mask
{
    public function match(string $key): bool
    {
        // does not really matter, we always match
        return true;
    }

    protected function applyToNormalized($value): array
    {
        return \is_array($value)
            ? $this->applyToArray($value)
            : $this->applyToObject($value);
    }

    private function applyToArray(array $value): array
    {
        $items = [];
        foreach ($value as $item) {
            $subResult = $this->applyToNormalized($item);
            if ([] !== $subResult) {
                $items[] = $subResult;
            }
        }
        return $items;
    }

    private function applyToObject($value): array
    {
        $results = [];
        foreach ($this->children as $subMaskElement) {
            $results[] = $subMaskElement->filter($value);
        }
        return array_merge([], ...$results);
    }
}
