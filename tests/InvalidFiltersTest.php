<?php

namespace messere\phpValueMask;

use messere\phpValueMask\Parser\Parser;
use messere\phpValueMask\Parser\ParserException;
use PHPUnit\Framework\TestCase;

class InvalidFiltersTest extends TestCase
{
    /**
     * @dataProvider invalidFilterProvider
     */
    public function testInvalidFilters(string $filter): void
    {
        $parser = new Parser();
        $this->expectException(ParserException::class);
        $parser->parse($filter);
    }

    public function invalidFilterProvider(): array
    {
        return [
            [''],
            ['123'],
            ['1z'],
            ['%a'],
            ['^a'],
            ['a,**'],
            ['a*'],
            ['a('],
            ['a()'],
            ['a(b'],
            ['a((b)'],
            ['a((b))'],
            ['a(b))'],
            ['a//b'],
            ['a,/a'],
            ['a(/)'],
            ['01(a)'],
            ['(a)'],
        ];
    }
}
