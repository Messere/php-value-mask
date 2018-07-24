# php-value-mask

[![Packagist](https://img.shields.io/packagist/dt/messere/php-value-mask.svg)](https://packagist.org/packages/messere/php-value-mask)

## Purpose

Google in their [Performance Tips](https://developers.google.com/discovery/v1/performance#partial) for
APIs, suggest to limit required bandwidth by filtering out unused fields in response. Their APIs 
support additional URL parameter `fields` which asks API to include only specific fields in response.   

`fields` parameter follows a simple syntax, which allows to query for nested keys, multiple keys 
or use wildcard to include all fields (see *Syntax* and *Grammar* sections below).

This library implements parsing of `fields` parameter and filtering of arrays / objects.  

## Usage example

```php
<?php
require_once 'vendor/autoload.php';

use messere\phpValueMask\Parser\Parser;
use messere\phpValueMask\Parser\ParserException;

$parser = new Parser();

$input = [
    'id' => 1,
    'resource' => 'book',
    'title' => 'Good Omens',
    'identifiers' => (object)[
        'isbn' => 'ISBN 83-85100-63-6​',
        'amazon' => '0060853980',  
    ],
    'authors' => [
        [
            'firstName' => 'Terry',
            'lastName' => 'Pratchett'
        ],
        [
            'firstName' => 'Neil',
            'lastName' => 'Gaiman'
        ],
    ],
    'year' => [
        'us' => 1990,
        'uk' => 1990,
        'pl' => 1992,
    ],
    'publisher' => [
        'us' => 'Workman',
        'uk' => 'Gollancz',
        'pl' => 'CIA-Books-SVARO',
    ],
];

$filter = 'title,identifiers/isbn,authors/firstName,*(us,uk),keywords';

try {
    $filteredInput = $parser->parse($filter)->filter($input);
    print_r($filteredInput);
} catch (ParserException $e) {
    echo 'Parser error: ' . $e->getMessage();
} 
```

Let's analyze elements of used filter:

- `title` matches top level element with key title ('Good Omens')
- `identifiers/isbn` matches top level element `identifiers` and 
  then includes `isbn` element from matched object ('ISBN 83-85100-63-6')
- `authors/firstName` finds an array of elements (list) under the key `authors`
  and examines all elements, extracting `firstName` from each. ('Terry' and 'Neil')
- `*(us,uk)` examines all properties and extracts fields `us` and `uk`. ('1990' and '1990'
from `year` element, 'Workman', 'Gollancz' from `publisher`)
- `keywords` does not match anything and is silently ignored.

As a result we expect the following output:

```php
Array
(
    [title] => Good Omens
    [identifiers] => Array
        (
            [isbn] => ISBN 83-85100-63-6​
        )

    [authors] => Array
        (
            [0] => Array
                (
                    [firstName] => Terry
                )

            [1] => Array
                (
                    [firstName] => Neil
                )

        )

    [year] => Array
        (
            [us] => 1990
            [uk] => 1999
        )

    [publisher] => Array
        (
            [us] => Workman
            [uk] => Gollancz
        )

)
```

ready to serialize to `JSON`, etc.

Note that library does preserve the structure/nesting of values, but not
necessarily types of values - all objects are converted to associative arrays
with object's public properties as keys.

Another example:

```php
<?php
require_once 'vendor/autoload.php';

use messere\phpValueMask\Parser\Parser;
use messere\phpValueMask\Parser\ParserException;

$parser = new Parser();

$input = json_decode(
    file_get_contents('http://xkcd.com/257/info.0.json')
);

$mask = 'title,img,alt';

try {
    $filteredInput = $parser->parse($mask)->filter($input);
    echo sprintf(
        '![%s](%s "%s")',
        $filteredInput['title'],
        $filteredInput['img'],
        $filteredInput['alt']
    );
} catch (ParserException $e) {
    echo 'Parser error: ' . $e->getMessage();
}
```

Returns Markdown that renders the following:

![Code Talkers](https://imgs.xkcd.com/comics/code_talkers.png "As far as I can tell, Navajo doesn't have a common word for 'zero'.  do-neh-lini means 'neutral'.")

## Syntax

- `a` selects key `a` from input
- `a,b,c` comma separated list of elements: selects keys `a` and `b` and `c`
- `a/b/c` nested elements: selects key `c` from parent element `b` 
    which in turn has parent element `a`
- `a(b,c)` multiple elements: selects elements `b` and `c` from parent element `a`
- `a(b,c/d)` multiple elements: from parent `a` select element `b` and element `d` 
    nested in `c`
- `a/*/c` wildcard: selects element `c` from all children of element `a`
- `*(b,c)` wildcard: selects elements `b` and `c` from any parent   

Etc. See tests for more examples as well as examples of invalid filters. 

## Grammar

Since Google does not provide detailed grammar for their
"fields" language, this package uses the following arbitrarily
selected rules, that in author's opinion closely resamble intent
of original authors. 
 
In EBNF notation:

```text
Mask         = MaskElement | MaskElement , "," , Mask ;
MaskElement  = ArrayOfMasks | NestedKeys ;
ArrayOfMasks = Key , "(" , Mask , ")" ;
NestedKeys   = Key , [ "/" , NestedKeys ] ;
Key          = Wildcard | Identifier ;
Identifier   = Letter , { Letter | Digit }
Wildcard     = "*" ;
Letter       = "A" | "B" | "C" | "D" | "E" | "F" | "G" | "H" | "I" | "J" | "K" | "L" | "M" |
               "N" | "O" | "P" | "Q" | "R" | "S" | "T" | "U" | "V" | "W" | "X" | "Y" | "Z" |
               "a" | "b" | "c" | "d" | "e" | "f" | "g" | "h" | "i" | "j" | "k" | "l" | "m" |
               "n" | "o" | "p" | "q" | "r" | "s" | "t" | "u" | "v" | "w" | "x" | "y" | "z" |
               "_";
Digit        = "1" | "2" | "3" | "4" | "5" | "6" | "7" | "8" | "9" | "0" ;
```

## Acknowledgements

Library is inspired by:

* Google's [API performance tips](https://developers.google.com/discovery/v1/performance#partial).
* Similar JavaScript library: [nemtsov/json-mask](https://github.com/nemtsov/json-mask).

## License

[MIT](License)
