<?php

namespace messere\phpValueMask\Mask;

use messere\phpValueMask\Parser\Parser;
use messere\phpValueMask\Parser\ParserException;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    private function assertFilteringResult(array $expected, $input, string $filter): void
    {
        try {
            $this->assertEquals(
                $expected,
                $this->parser->parse($filter)->filter($input)
            );
        } catch (ParserException $e) {
            $this->fail('Parsing exception: ' . $e->getMessage());
        }
    }

    public function testSingleKey(): void
    {
        $mask = 'a1';

        $input = [
            'a1' => 1,
            'b2' => 2,
        ];

        $expected = [
            'a1' => 1,
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testTwoKeys(): void
    {
        $mask = 'a1,b2';

        $input = [
            'a1' => 1,
            'b2' => 2,
            'c3' => 3,
        ];

        $expected = [
            'a1' => 1,
            'b2' => 2,
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testTwoKeysOneDoesNotExist(): void
    {
        $mask = 'a1,d4';

        $input = [
            'a1' => 1,
            'b2' => 2,
            'c3' => 3,
        ];

        $expected = [
            'a1' => 1
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testTwoKeysRepeated(): void
    {
        $mask = 'a1,a1';

        $input = [
            'a1' => 1,
            'b2' => 2,
            'c3' => 3,
        ];

        $expected = [
            'a1' => 1
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testWildcard(): void
    {
        $mask = '*';

        $input = [
            'a1' => 1,
            'b2' => 2,
            'c3' => 3,
        ];

        $expected = $input;

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testWildcardAndKey(): void
    {
        $mask = 'a1,*';

        $input = [
            'a1' => 1,
            'b2' => 2,
            'c3' => 3,
        ];

        $expected = $input;

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testKeyAndWildcard(): void
    {
        $input = [
            'a1' => 1,
            'b2' => 2,
            'c3' => 3,
        ];

        $mask = '*,a1';

        $expected = $input;

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testNested(): void
    {
        $input = [
            'a1' => [
                'a2' => 2,
            ],
            'a2' => 3
        ];

        $mask = 'a1/a2';

        $expected = [
            'a1' => [
                'a2' => 2
            ]
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testNestedWithWildcard(): void
    {
        $input = [
            'a1' => [
                'a2' => 2,
            ],
            'a2' => 3,
            'a3' => [
                'a2' => 4,
            ],
        ];

        $mask = '*/a2';

        $expected = [
            'a1' => [
                'a2' => 2
            ],
            'a3' => [
                'a2' => 4
            ]
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }


    public function testDeepNested(): void
    {
        $input = [
            'a1' => [
                'a2' => [
                    'a3' => 1,
                    'b1' => 4
                ]
            ],
            'a2' => 3,
            'a3' => [
                'a2' => 4,
            ],
        ];

        $mask = 'a1/a2/a3';

        $expected = [
            'a1' => [
                'a2' => [
                    'a3' => 1
                ]
            ],
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testArrayOfMasks(): void
    {
        $input = [
            'a1' => [
                'a1' => 1,
                'a2' => 2,
                'a3' => 3,
                'a4' => 4
            ],
            'a2' => [
                'a1' => 5,
                'a2' => 6,
                'a3' => 7,
                'a4' => 8
            ],
        ];

        $mask = 'a1(a2,a4)';

        $expected = [
            'a1' => [
                'a2' => 2,
                'a4' => 4
            ]
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testArrayOfMasksWithWildcard(): void
    {
        $input = [
            'a1' => [
                'a1' => 1,
                'a2' => 2,
                'a3' => 3,
                'a4' => 4
            ],
            'a2' => [
                'a1' => 5,
                'a2' => 6,
                'a3' => 7,
                'a4' => 8
            ],
        ];

        $mask = 'a1(*)';

        $expected = [
            'a1' => [
                'a1' => 1,
                'a2' => 2,
                'a3' => 3,
                'a4' => 4
            ]
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testWildcardArrayOfMasks(): void
    {
        $input = [
            'a1' => [
                'a1' => 1,
                'a2' => 2,
                'a3' => 3,
                'a4' => 4
            ],
            'a2' => [
                'a1' => 5,
                'a2' => 6,
                'a3' => 7,
                'a4' => 8
            ],
        ];

        $mask = '*(a1,a4)';

        $expected = [
            'a1' => [
                'a1' => 1,
                'a4' => 4
            ],
            'a2' => [
                'a1' => 5,
                'a4' => 8
            ],
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testArrayOfMasksWithNested(): void
    {
        $input = [
            'a1' => [
                'b1' => 1,
                'b2' => [
                    'c1' => 9,
                    'a1' => 10,
                ],
                'b3' => 3,
                'b4' => 4
            ],
            'a2' => [
                'b1' => 5,
                'b2' => 6,
                'b3' => 7,
                'b4' => 8
            ],
        ];

        $mask = 'a1(b1,b2/c1)';

        $expected = [
            'a1' => [
                'b1' => 1,
                'b2' => [
                    'c1' => 9
                ]
            ],
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testSingleKeyOnArray(): void
    {
        $input = [
            [
                'a1' => 1,
                'b2' => 2,
            ],
            [
                'a1' => 3,
                'b2' => 4,
            ],
            [
                'a1' => 5,
                'b2' => 6,
            ],
            [
                'b2' => 7,
            ],
        ];

        $mask = 'a1';

        $expected = [
            [ 'a1' => 1 ],
            [ 'a1' => 3 ],
            [ 'a1' => 5 ],
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testTwoKeysOnArray(): void
    {
        $input = [
            [
                'a1' => 1,
                'b2' => 2,
            ],
            [
                'a1' => 3,
                'b2' => 4,
            ],
            [
                'a1' => 5,
                'b2' => 6,
            ],
            [
                'b2' => 7,
            ],
        ];

        $mask = 'a1,b2';

        $expected = 
            $input
        ;

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function testTwoKeysRepeatedOnArray(): void
    {
        $input = [
            [
                'a1' => 1,
                'b2' => 2,
            ],
            [
                'a1' => 3,
                'b2' => 4,
            ],
            [
                'a1' => 5,
                'b2' => 6,
            ],
            [
                'b2' => 7,
            ],
        ];

        $mask = 'a1,a1';

        $expected = [
            [ 'a1' => 1 ],
            [ 'a1' => 3 ],
            [ 'a1' => 5 ],
         ];

        $this->assertFilteringResult($expected, $input, $mask);
    }

    public function OverlappingArrayAndNestedOnArray(): void
    {
        $input = [
            [
                'a1' => [
                    'a2' => 8,
                ],
                'b2' => 2,
            ],
            [
                'a1' => 3,
                'b2' => 4,
            ],
            [
                'a1' => [
                    'a2' => 9
                ],
                'b2' => 6,
            ],
            [
                'b2' => 7,
            ],
        ];

        $mask = 'a1(a2),a1/a2';

        $expected = [
            [
                'a1' => [
                    'a2' => 8,
                ],
            ],
            [
                'a1' => [
                    'a2' => 9
                ],
            ],
        ];

        $this->assertFilteringResult($expected, $input, $mask);
    }
}
