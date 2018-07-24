<?php

namespace messere\phpValueMask\Mask;

use messere\phpValueMask\Parser\Parser;
use messere\phpValueMask\Parser\ParserException;
use PHPUnit\Framework\TestCase;

class IdentifierTest extends TestCase
{
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    /**
     * @throws ParserException
     */
    public function testSingleKeyWithAllValidCharsInName(): void
    {
        $mask = 'abcdefghijklmnopqrstuvwxyz_ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789';

        $input = [
            'a1' => 1,
            'b2' => 2,
        ];

        $this->assertEquals([], $this->parser->parse($mask)->filter($input));
    }

}
