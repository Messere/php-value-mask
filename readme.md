# php-value-mask

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
