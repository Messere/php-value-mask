<?php

namespace messere\phpValueMask\Parser;

class Input
{
    private $value;
    private $currentPosition = 0;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getChar(): ?string
    {
        $char = $this->value{$this->currentPosition} ?? null;
        $this->currentPosition++;
        return $char;
    }

    public function mark(): int
    {
        return $this->currentPosition;
    }

    public function rewind(int $mark): void
    {
        $this->currentPosition = $mark;
    }

    public function isConsumed(): bool
    {
        return $this->currentPosition === \strlen($this->value);
    }

    public function maybeConsume(callable $callback)
    {
        $mark = $this->mark();
        $result = $callback();
        if (null === $result) {
            $this->rewind($mark);
        }
        return $result;
    }

    public function maybeConsumeTerminal(string $terminal): ?string
    {
        $mark = $this->mark();
        $char = $this->getChar();

        if ($char !== $terminal) {
            $this->rewind($mark);
            return null;
        }
        return $char;
    }

    public function maybeConsumeTerminalByRegexp(string $terminalMatch): ?string
    {
        $mark = $this->mark();
        $char = $this->getChar();
        if (1 === preg_match($terminalMatch, $char)) {
            return $char;
        }
        $this->rewind($mark);
        return null;
    }

    public function consumed(): string
    {
        return \substr($this->value, 0, $this->currentPosition);
    }

    public function left(): string
    {
        return \substr($this->value, $this->currentPosition);
    }
}
