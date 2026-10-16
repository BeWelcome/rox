<?php

namespace Mike42\Wikitext;

class ParserLineBlockElement
{
    public $startChar;  /* Characters which can loop to start this element */
    public $endChar;    /* End character */
    public $limit;      /* Max depth of the element */
    public $nestTags;   /* True if the tags for this element need to made hierachical for nesting */
    
    public function __construct($startChar, $endChar, $limit = 0, $nestTags = true)
    {
        $this -> startChar = $startChar;
        $this -> endChar = $endChar;
        $this -> limit = $limit;
        $this -> nestTags = $nestTags;
    }
}
