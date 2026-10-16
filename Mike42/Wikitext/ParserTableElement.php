<?php

namespace Mike42\Wikitext;

class ParserTableElement
{
    public $lineStart;  /* Token appearing at start of line */
    public $argsep;
    public $limit;
    public $inlinesep;

    public function __construct(string $lineStart, string $argsep, string $inlinesep, $limit)
    {
        $this -> lineStart = str_split($lineStart);
        $this -> argsep = str_split($argsep);
        $this -> inlinesep = str_split($inlinesep);
        $this -> limit = $limit;
    }
}
