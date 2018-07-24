<?php

namespace messere\phpValueMask;

use messere\phpValueMask\Parser\Parser;
use messere\phpValueMask\Parser\ParserException;
use PHPUnit\Framework\TestCase;

class InvalidFiltersTest extends TestCase
{
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    /**
     * @dataProvider invalidFilterProvider
     * @param string $filter
     */
    public function testInvalidFilters(string $filter): void
    {
        $error = '';
        try {
            $this->parser->parse($filter);
        } catch (ParserException $e) {
            $error = $e->getMessage();
        }
        $this->assertNotEmpty(
            $error,
            "Expected parsing error on invalid filter $filter, but none was thrown"
        );
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
