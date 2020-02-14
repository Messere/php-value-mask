<?php

namespace messere\phpValueMask\Mask;

use messere\phpValueMask\Parser\Parser;
use PHPUnit\Framework\TestCase;

class IdentifierTest extends TestCase
{
    public function testSingleKeyWithAllValidCharsInName(): void
    {
        $mask = 'abcdefghijklmnopqrstuvwxyz_ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789';

        $input = [
            'a1' => 1,
            'b2' => 2,
        ];

        $parser = new Parser();
        $this->assertEquals([], $parser->parse($mask)->filter($input));
    }
}
