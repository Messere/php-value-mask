<?php

namespace messere\phpValueMask\Mask;

abstract class Mask implements IMask
{
    /**
     * @var Mask[]
     */
    protected $children = [];
    private $stopAfterNumberOfMatches;
    private $childrenLimit;

    public function __construct(
        int $stopAfterNumberOfMatches = null,
        int $childrenLimit = null
    ) {
        $this->stopAfterNumberOfMatches = $stopAfterNumberOfMatches;
        $this->childrenLimit = $childrenLimit;
    }

    /**
     * apply current mask to value, return filtered out value
     * note that it won't retain original object types, all values will be converted to array
     * @param $value
     * @return array
     */
    public function apply($value): array
    {
        // normalize associative arrays to stdClasses so we don't have to deal
        // with detecting if array value is in fact "list" or "object"
        $valueNormalized = json_decode(json_encode($value));
        return $this->applyToNormalized($valueNormalized);
    }

    public function addChild(IMask $child): void
    {
        if (null === $this->childrenLimit || \count($this->children) < $this->childrenLimit) {
            $this->children[] = $child;
        } else {
            throw new MaskConfigurationException('Mask children limit exceeded');
        }
    }

    /**
     * check if key matches current mask
     * @param string $key
     * @return bool
     */
    abstract public function match(string $key): bool;

    /**
     * check if class has any children
     * @return bool
     */
    private function hasChildren(): bool
    {
        return [] !== $this->children;
    }

    /**
     * append values to result if not empty
     * @param null|string $key
     * @param array $result
     * @param array $values
     */
    private function maybeAppend(?string $key, array &$result, array $values): void
    {
        if ([] !== $values) {
            if (null === $key) {
                $result[] = $values;
            } else {
                $result[$key] = $values;
            }
        }
    }

    private function applyToObject($value): array
    {
        $result = [];
        $numberOfMatches = 0;
        foreach ((array)$value as $key => $val) {
            if ($this->matchedEnough($numberOfMatches)) {
                break;
            }

            if (!$this->match($key)) {
                continue;
            }

            $numberOfMatches++;

            if ($this->hasChildren()) {
                $this->maybeAppend($key, $result, $this->children[0]->apply($val));
                continue;
            }

            $result[$key] = $val;
        }
        return $result;
    }

    protected function applyToNormalized($valueNormalized): array
    {
        $result = [];

        if (\is_object($valueNormalized)) {
            $result = array_merge($result, $this->applyToObject($valueNormalized));
        }

        if (\is_array($valueNormalized)) {
            $subResult = [];
            foreach ($valueNormalized as $item) {
                $this->maybeAppend(null, $subResult, $this->applyToNormalized($item));
            }
            $result = array_merge($result, $subResult);
        }

        return $result;
    }

    private function matchedEnough(int $numberOfMatches): bool
    {
        return $this->stopAfterNumberOfMatches !== null && $numberOfMatches >= $this->stopAfterNumberOfMatches;
    }
}
