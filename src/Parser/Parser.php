<?php

namespace messere\phpValueMask\Parser;

use messere\phpValueMask\Mask\IMask;
use messere\phpValueMask\Mask\Mask;
use messere\phpValueMask\Mask\MaskAny;
use messere\phpValueMask\Mask\MaskArray;
use messere\phpValueMask\Mask\MaskOne;

class Parser
{
    /**
     * @var Input
     */
    private $input;

    /**
     * @param string $valueMaskDefinition
     * @return IMask
     * @throws ParserException
     */
    public function parse(string $valueMaskDefinition): IMask
    {
        $this->input = new Input($valueMaskDefinition);
        $root = $this->parseMask();
        if (!$this->input->isConsumed()) {
            throw new ParserException(
                'Invalid input, parser stopped in the middle of input'
            );
        }
        if (null === $root) {
            throw new ParserException('Invalid input, unrecognized input');
        }
        return $root;
    }

    private function parseMask(?MaskArray $root = null): ?IMask
    {
        $root = $root ?? new MaskArray();

        $maskElement = $this->input->maybeConsume(function () {
            return $this->parseMaskElement();
        });

        if (null === $maskElement) {
            return null;
        }
        $root->addChild($maskElement);

        if (null === $this->input->maybeConsumeTerminal(',')) {
            return $root;
        }
        return $this->parseMask($root);
    }

    private function parseMaskElement(): ?IMask
    {
        $node = $this->input->maybeConsume(function () {
            return $this->parseArrayOfMasks();
        });
        if (null !== $node) {
            return $node;
        }

        return $this->input->maybeConsume(function () {
            return $this->parseNestedKeys();
        });
    }

    private function parseArrayOfMasks(): ?IMask
    {
        /**
         * @var $keyNode IMask
         */
        $keyNode = $this->input->maybeConsume(function (): ?IMask {
            return $this->parseKey();
        });
        if (null === $keyNode || null === $this->input->maybeConsumeTerminal('(')) {
            return null;
        }

        $maskNode = $this->input->maybeConsume(function (): ?IMask {
            return $this->parseMask();
        });
        if (null === $maskNode || null === $this->input->maybeConsumeTerminal(')')) {
            return null;
        }

        $keyNode->addChild($maskNode);

        return $keyNode;
    }

    private function parseNestedKeys(): ?IMask
    {
        /**
         * @var $keyNode IMask
         */
        $keyNode = $this->input->maybeConsume(function (): ?IMask {
            return $this->parseKey();
        });

        if (null === $keyNode || null === $this->input->maybeConsumeTerminal('/')) {
            return $keyNode;
        }

        $moreNestedKeys = $this->input->maybeConsume(function () {
            return $this->parseNestedKeys();
        });

        if (null === $moreNestedKeys) {
            return null;
        }

        $keyNode->addChild($moreNestedKeys);
        return $keyNode;
    }

    private function parseKey(): ?Mask
    {

        $wildcard = $this->input->maybeConsume(function () {
            return $this->parseWildcard();
        });

        if (null !== $wildcard) {
            return new MaskAny();
        }

        $identifier = $this->input->maybeConsume(function () {
            return $this->parseIdentifier();
        });
        if (null !== $identifier) {
            return new MaskOne($identifier);
        }

        return null;
    }

    private function parseIdentifier(): ?string
    {
        $identifier = $this->input->maybeConsume(function () {
            $identifier = '';
            $firstChar = $this->input->maybeConsumeLetter();
            if (null === $firstChar) {
                return null;
            }
            $identifier .= $firstChar;

            do {
                $char = $this->input->maybeConsumeLetterOrDigit();
                if ($char === null) {
                    break;
                }
                $identifier .= $char;
            } while (true);
            return $identifier;
        });

        if ($identifier === null) {
            return null;
        }

        return $identifier;
    }

    private function parseWildcard(): ?string
    {
        return $this->input->maybeConsumeTerminal('*');
    }
}
