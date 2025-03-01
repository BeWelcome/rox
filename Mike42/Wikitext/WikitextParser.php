<?php

/*
  Library to add wikitext support to a web app -- http://mike.bitrevision.com/wikitext/

  Copyright (C) 2012 Michael Billington <michael.billington@gmail.com>

  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
  associated documentation files (the "Software"), to deal in the Software without restriction,
  including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
  and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
  subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all copies or substantial
  portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
  INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
  PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
  CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
  SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Mike42\Wikitext;

class WikitextParser
{

    const VERSION = "1.0";
    const MAX_INCLUDE_DEPTH = 32; /* Depth of template includes to put up with. set to 0 to disallow inclusion, negative to remove the limit */

    private $inline;
    private $lineBlock;
    protected $backend;
    private $tableBlock;
    private $tableStart;
    private $inlineLookup;
    private $inlineChars;
    private $preprocessor;
    private $preprocessorChars;

    /* These are set as a result of parsing */
    public $preprocessed; /* Wikitext after preprocessor had a go at it. */
    public $result; /* Wikitext of result */

    /**
     * Definitions for tokens with special meaning to the parser
     */
    public function __construct(HtmlRenderer $render)
    {
        /* Table elements. These are parsed separately to the other elements */
        $this->tableStart = new ParserInlineElement("{|", "|}");

        $this->tableBlock = array(
            'tr' => new ParserTableElement('|-', '', '', ''),
            'th' => new ParserTableElement('!', '|', '!!', 1),
            'td' => new ParserTableElement('|', '|', '||', 1),
            'caption' => new ParserTableElement('|+', '', '', 0));

        /* Inline elemens. These are parsed recursively and can be nested as deeply as the system will allow. */
        $this->inline = array(
            'nothing' => new ParserInlineElement('', ''),
            'td' => new ParserInlineElement('', ''), // Just used as a marker
            'linkInternal' => new ParserInlineElement('[[', ']]', '|', '='),
            'linkExternal' => new ParserInlineElement('[', ']', ' ', '', 1),
            'bold' => new ParserInlineElement("'''", "'''"),
            'italic' => new ParserInlineElement("''", "''"),
            'switch' => new ParserInlineElement('__', '__'));
        $this->inlineChars = [
            '[' => true,
            '\'' => true,
            ']' => true,
            "\n" => true,
            "=" => true,
            "*" => true,
            "#" => true,
            ":" => true,
            "|" => true,
            "~" => true,
            " " => true,
            '_' => true
        ];

        /* Create lookup table for efficiency */

        $this->inlineLookup = $this->elementLookupTable($this->inline);
        $this->backend = $render;

        /* Line-block elements. These are characters which have a special meaning at the start of lines, and use the next end-line as a close tag. */
        $this->lineBlock = array(
            'pre' => new ParserLineBlockElement(array(" "), [], 1, false),
            'ul' => new ParserLineBlockElement(array("*"), [], 32, true),
            'ol' => new ParserLineBlockElement(array("#"), [], 32, true),
            'dl' => new ParserLineBlockElement(array(":", ";"), [], 32, true),
            'h' => new ParserLineBlockElement(array("="), array("="), 6, false));

        $this->preprocessor = array(
            'noinclude' => new ParserInlineElement('<noinclude>', '</noinclude>'),
            'includeonly' => new ParserInlineElement('<includeonly>', '</includeonly>'),
            'arg' => new ParserInlineElement('{{{', '}}}', '|', '', 1),
            'template' => new ParserInlineElement('{{', '}}', '|', '='),
            'comment' => new ParserInlineElement('<!--', '-->'));
        $this->preprocessorChars = [
            '<' => true,
            '=' => true,
            '|' => true,
            '{' => true
        ];

        $this->initialised = true;
    }

    private function elementLookupTable(array $elements): array
    {
        $lookup = [];
        foreach ($elements as $key => $token) {
            if (count($token->startTag) != 0) {
                $c = $token->startTag[0];
                if (!isset($lookup[$c])) {
                    $lookup[$c] = [];
                }
                $lookup[$c][$key] = $elements[$key];
            }
        }
        return $lookup;
    }

    /**
     * Parse a given document/page of text (main entry point)
     *
     * @param string $text
     */
    public function parse(string $text): string
    {
        $this->preprocessed = $this->preprocessText($this->explodeString($text));

        /* Now divide into paragraphs */
        // TODO operate on arrays instead of strings here
        $sections = explode("\n\n", str_replace("\r\n", "\n", $this->preprocessed));

        $newtext = [];
        foreach ($sections as $section) {
            /* Newlines at the start/end have special meaning (compare to how this is called from parseLineBlock) */
            $sectionChars = $this->explodeString("\n" . $section);
            $result = $this->parseInline($sectionChars, 'p');
            $newtext[] = $result['parsed'];
        }
        return $this->result = implode($newtext);
    }

    private function explodeString(string $string)
    {
        return $chrArray = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Handle template arguments and other oddities. This section of the parser is single-pass and linear, with the exception of the part which substitutes templates
     * @param string $text wikitext to handle
     * @param mixed $arg Arguments (applies only to templates)
     * @param boolean $included true if the text is included, false otherwise
     * @return string
     */
    private function preprocessText(array $textChars, array $arg = [], bool $included = false, int $depth = 0)
    {
        $parsed = '';
        $len = count($textChars);
        for ($i = 0; $i < $len; $i++) {
            $hit = false;
            $c = $textChars[$i];
            if (!isset($this->preprocessorChars[$c])) {
                /* Fast exit for characters that do not start a tag. */
                // TODO Could work faster if we didn't concatenate each character
                $parsed .= $c;
                continue;
            }
            foreach ($this->preprocessor as $key => $child) {
                if ($this->tagIsAt($child->endTag, $textChars, $i)) {
                    if (($key == 'includeonly' && $included) || ($key == 'noinclude' && !$included)) {
                        $hit = true;
                        $i += count($child->endTag);

                        /* Ignore expected end-tags */
                        break;
                    }
                }

                if ($this->tagIsAt($child->startTag, $textChars, $i)) {
                    /* Hit a symbol. Parse it and keep going after the result */
                    $hit = true;
                    $i += count($child->startTag);

                    if (($key == 'includeonly' && $included) || ($key == 'noinclude' && !$included)) {
                        /* If this is a good tag, ignore it! */
                        break;
                    }

                    /* Seek until end tag, looking for splitters */
                    $innerArg = [];
                    $innerBuffer = '';
                    $innerCurKey = '';

                    for ($i = $i; $i < $len; $i++) {
                        $innerHit = false;

                        if ($this->tagIsAt($child->endTag, $textChars, $i)) {
                            $i += count($child->endTag);
                            /* Clear buffers now */
                            if ($innerCurKey == '') {
                                array_push($innerArg, $innerBuffer);
                            } else {
                                $innerArg[$innerCurKey] = $innerBuffer;
                            }

                            /* Figure out what to do with data */
                            $innerCurKey = array_shift($innerArg);
                            if ($key == 'arg') {
                                if (is_numeric($innerCurKey)) {
                                    $innerCurKey -= 1; /* Because the associative array will be starting at 0 */
                                }
                                if (isset($arg[$innerCurKey])) {
                                    $parsed .= $arg[$innerCurKey];      // Use arg value if set
                                } elseif (count($innerArg) > 0) {
                                    $parsed .= array_shift($innerArg);  // Otherwise use embedded default if set
                                }
                            } else if ($key == 'template') {
                                /* Load wikitext of template, and preprocess it */
                                if (self::MAX_INCLUDE_DEPTH < 0 || $depth < self::MAX_INCLUDE_DEPTH) {
                                    $markup = trim($this->backend->getTemplateMarkup($innerCurKey));
                                    $parsed .= $this->preprocessText($this->explodeString($markup), $innerArg, true, $depth + 1);
                                }
                            }

                            $innerCurKey = ''; // Reset key
                            $innerBuffer = ''; // Reset parsed values
                            break; /* Stop inner loop(hit) */
                        }

                        /* Argument splitting -- A dumber, non-recursiver version of what is used in ParseInline() */
                        if ($child->hasArgs && ($child->argLimit == 0 || $child->argLimit > count($innerArg))) {
                            if ($this->tagIsAt($child->argSep, $textChars, $i)) {
                                /* Hit argument separator */
                                if ($innerCurKey == '') {
                                    array_push($innerArg, $innerBuffer);
                                } else {
                                    $innerArg[$innerCurKey] = $innerBuffer;
                                }
                                $innerCurKey = ''; // Reset key
                                $innerBuffer = ''; // Reset parsed values
                                $i += count($child->argSep) - 1;
                                $innerHit = true;
                            } elseif ($innerCurKey == '' && $this->tagIsAt($child->argNameSep, $textChars, $i)) {
                                /* Hit name/argument splitter */
                                $innerCurKey = $innerBuffer; // Set key
                                $innerBuffer = '';  // Reset parsed values
                                $i += count($child->argNameSep) - 1;
                                $innerHit = true;
                            }
                        }

                        if (!$innerHit) {
                            /* Append non-matching characters to buffer as we go */
                            $innerBuffer .= $textChars[$i];
                        }
                    }
                }
            }

            /* Add non-affected characters as we go */
            if (!$hit) {
                $parsed .= $c;
            } else {
                $i -= 1;
            }
        }
        return $parsed;
    }

    private function tagIsAt(array $tag, array $textChars, int $position)
    {
        if ($position >= count($textChars)) {
            // Fast exit for common case
            return false;
        }
        if (isset($tag[0]) && $textChars[$position] != $tag[0]) {
            // Fast exit for common case
            return false;
        }

        // More detailed checks for other cases
        $tagLen = count($tag);
        $strLen = count($textChars);
        $match = $position + $tagLen <= $strLen && $tagLen > 0;
        for ($i = 1; $i < $tagLen && $match; $i++) {
            if ($textChars[$position + $i] !== $tag[$i]) {
                $match = false;
                break;
            }
        }
        return $match;
    }

    /**
     * Parse a block of wikitext looking for inline tokens, indicating the start of an element.
     * Calls itself recursively to search inside those elements when it finds them
     *
     * @param string $text Text to parse
     * @param $token The name of the current inline element, if inside one.
     */
    private function parseInline(array $textChars, string $token = '', $idxFrom = 0)
    {
        /* Quick escape if we've run into a table */
        $inParagraph = false;
        if ($token == '' || !isset($this->inline[$token])) {
            /* Default to empty token if none is set (these have no end token, ensuring there will be no remainder after this runs) */
            if ($token == 'p') {
                /* Blocks of text here need to be encapsualted in paragraph tags */
                $inParagraph = true;
            }
            $inlineElement = $this->inline['nothing'];
        } else {
            $inlineElement = $this->inline[$token];
        }

        $parsed = ''; // For completely parsed text
        $buffer = ''; // For text which may still be encapsulated or chopped up
        $remainder = '';

        $arg = [];
        $curKey = '';

        $len = count($textChars);
        for ($i = $idxFrom; $i < $len; $i++) {
            /* Looping through each character */
            $hit = false; // State so that the last part knows whether to simply append this as an unmatched character
            $c = $textChars[$i];
            if (!isset($this->inlineChars[$c])) {
                // Fast exit for characters that do not start a tag.
                // TODO Could work faster if we didn't concatenate each character
                $buffer .= $c;
                continue;
            }

            /* Looking for this element's close-token */
            if ($this->tagIsAt($inlineElement->endTag, $textChars, $i)) {
                /* Hit a close tag: Stop parsing here, return the remainder, and let the parent continue */
                $start = $i + count($inlineElement->endTag);

                if ($inlineElement->hasArgs) {
                    /* Handle arguments if needed */
                    if ($curKey == '') {
                        array_push($arg, $buffer);
                    } else {
                        $arg[$curKey] = $buffer;
                    }
                    $buffer = $this->backend->renderWithArgs($token, $arg);
                }

                /* Clean up and quit */
                $parsed .= $buffer; /* As far as I can tall $inPargraph should always be false here? */
                return array('parsed' => $parsed, 'remainderIdx' => $start);
            }

            /* Next priority is looking for this element's agument tokens if applicable */
            if ($inlineElement->hasArgs && ($inlineElement->argLimit == 0 || $inlineElement->argLimit > count($arg))) {
                if ($this->tagIsAt($inlineElement->argSep, $textChars, $i)) {
                    /* Hit argument separator */
                    if ($curKey == '') {
                        array_push($arg, $buffer);
                    } else {
                        $arg[$curKey] = $buffer;
                    }

                    $curKey = ''; // Reset key
                    $buffer = ''; // Reset parsed values
                    /* Handle position properly */
                    $i += count($inlineElement->argSep) - 1;
                    $hit = true;
                } elseif ($curKey == '' && $this->tagIsAt($inlineElement->argNameSep, $textChars, $i)) {
                    /* Hit name/argument splitter */
                    $curKey = $buffer; // Set key
                    $buffer = '';  // Reset parsed values
                    /* Handle position properly */
                    $i += count($inlineElement->argNameSep) - 1;
                    $hit = true;
                }
            }

            /* Looking for new open-tokens */
            if (isset($this->inlineLookup[$c])) {
                /* There are inline elements which start with this character. Check each one,.. */
                foreach ($this->inlineLookup[$c] as $key => $child) {
                    if (!$hit && $this->tagIsAt($child->startTag, $textChars, $i)) {
                        /* Hit a symbol. Parse it and keep going after the result */
                        $start = $i + count($child->startTag);

                        /* Regular, recursively-parsed element */
                        $result = $this->parseInline($textChars, $key, $start);
                        $buffer .= $this->backend->encapsulateElement($key, $result['parsed']);
                        $i = $result['remainderIdx'] - 1;
                        $hit = true;
                    }
                }
            }

            if (!$hit) {
                if ($c == "\n" && $i < $len - 1) {
                    if ($this->tagIsAt($this->tableStart->startTag, $textChars, $i + 1)) {
                        $hit = true;
                        $start = $i + 1 + count($this->tableStart->startTag);
                        $key = 'table';
                    } else {
                        /* Check for non-table line-based stuff coming up next, each time \n is found */
                        $next = $textChars[$i + 1];
                        foreach ($this->lineBlock as $key => $block) {
                            foreach ($block->startChar as $char) {
                                if (!$hit && $next == $char) {
                                    $hit = true;
                                    $start = $i + 1;
                                    break 2;
                                }
                            }
                        }
                    }

                    if ($hit) {
                        /* Go over what's been found */
                        if ($key == 'table') {
                            $result = $this->parseTable($textChars, $start);
                        } else {
                            /* Let parseLineBlock take care of this on a per-line basis */
                            $result = $this->parseLineBlock($textChars, $key, $start);
                        }
                        if ($buffer != '') {
                            /* Something before this was part of a paragraph */
                            $parsed .= $this->backend->encapsulateElement('paragraph', $buffer);
                            $inParagraph == true;
                        }
                        $buffer = "";
                        /* Now append this non-paragraph element */
                        $parsed .= $result['parsed'];
                        $i = $result['remainderIdx'] - 1;
                    }

                    /* Other \n-related things if it wasn't as exciting as above */
                    if ($buffer != '' && !$hit) {
                        /* Put in a space if it is not going to be the first thing added. */
                        $buffer .= " ";
                    }
                } else {
                    /* Append character to parsed output if it was not part of some token */
                    $buffer .= $c;
                }
            }

            if ($token == 'td') {
                /* We only get here from table syntax if something else was being parsed, so we can quit here */
                $parsed = $buffer;
                return array('parsed' => $parsed, 'remainderIdx' => $i);
            }
        }

        /* Need to throw argument-driven items at the backend first here */
        if ($inlineElement->hasArgs) {
            if ($curKey == '') {
                array_push($arg, $buffer);
            } else {
                $arg[$curKey] = $buffer;
            }
            $buffer = $this->backend->renderWithArgs($token, $arg);
        }

        if ($inParagraph && $buffer != '') {
            /* Something before this was part of a paragraph */
            $parsed .= $this->backend->encapsulateElement('paragraph', $buffer);
        } else {
            $parsed .= $buffer;
        }

        return array('parsed' => $parsed, 'remainderIdx' => $i);
    }

    /**
     * Parse block of wikitext known to be starting with a line-based token
     *
     * @param $text Wikitext block to parse
     * @param $token name of the LineBlock token which we suspect
     */
    private function parseLineBlock(array $textChars, string $token, $fromIdx = 0)
    {
        /* Block element we are using */
        $lineBlockElement = $this->lineBlock[$token];

        // Loop through lines
        $lineStart = $fromIdx;
        $list = [];
        while (($lineLen = $this->getLineLen($textChars, $lineStart)) !== false) {
            $startTokenLen = $this->countChar($lineBlockElement->startChar, $textChars, $lineStart, $lineBlockElement->limit);
            if ($startTokenLen === 0) {
                /* Wind back to include "\n" if the next line is not a list item. This is not expected
                 * to trigger on the first iteration, since line-block tags were found for calling this method.
                 */
                $lineStart -= 1;
                break;
            } else {
                $char = $textChars[$lineStart + $startTokenLen - 1];
                $endTokenLen = 0;
                if (count($lineBlockElement->endChar) > 0) {
                    /* Also need to cut off end letters, such as in == Heading == */
                    $endTokenLen = $this->countCharReverse($lineBlockElement->endChar, $textChars, $lineStart + $startTokenLen, $lineStart + $lineLen - 1);
                }
                /* Remainder of the line */
                $lineChars = array_slice($textChars, $lineStart + $startTokenLen, $lineLen - $startTokenLen - $endTokenLen);
                $result = $this->parseInline($lineChars);
                $list[] = array('depth' => $startTokenLen, 'item' => $result['parsed'], 'char' => $char);
            }
            /* Move along to start of next line */
            $lineStart += $lineLen + 1;
        }

        if ($lineBlockElement->nestTags) {
            /* Hierachy-ify nestable lists */
            $list = $this->makeList($list);
        }
        $parsed = $this->backend->renderLineBlock($token, $list);
        return array('parsed' => $parsed, 'remainderIdx' => $lineStart);
    }

    /**
     * Special handling for tables, uniquely containing both per-line and recursively parsed elements
     *
     * @param string $text Text to parse
     * @return multitype:string parsed and remaining text
     */
    private function parseTable(array $textChars, $fromIdx = 0)
    {
        $lineLen = $this->getLineLen($textChars, $fromIdx);
        $propertiesChars = array_slice($textChars, $fromIdx, $lineLen);
        $table['properties'] = implode($propertiesChars);
        $table['row'] = [];
        $lineStart = $lineLen + 1;
        while (($lineLen = $this->getLineLen($textChars, $lineStart)) !== false) {
            if ($this->tagIsAt($this->tableStart->endTag, $textChars, $lineStart)) {
                $lineStart += $lineLen + 1;
                break;
            }
            $hit = false;
            foreach ($this->tableBlock as $token => $block) {
                /* Looking for matching per-line elements */
                if (!$hit && $this->tagIsAt($block->lineStart, $textChars, $lineStart)) {
                    $hit = true;
                    break;
                }
            }
            if ($hit) {
                /* Move cursor along to skip the token */
                $tokenLen = count($block->lineStart);
                $contentStart = $lineStart + $tokenLen;
                $contentLen = $lineLen - $tokenLen;

                if ($token == 'td' || $token == 'th') {
                    if (!isset($tmpRow)) {
                        /* Been given a cell before a row. Make a row first */
                        $tmpRow = array('properties' => '', 'col' => []);
                    }
                    /* Clobber the remaining text together and throw it to the cell parser */
                    $result = $this->parseTableCells($token, $textChars, $contentStart, $tmpRow['col']);
                    $lineStart = $result['remainderIdx'];
                    $lineLen = -1;
                    $tmpRow['col'] = $result['col'];
                } elseif ($token == 'tr') {
                    $contentChars = array_slice($textChars, $contentStart, $contentLen);
                    if (isset($tmpRow)) {
                        /* Append existing row to table (if one exists) */
                        $table['row'][] = $tmpRow;
                    }
                    /* Clearing current row and set properties */
                    $tmpRow = array(
                        'properties' => implode($contentChars),
                        'col' => []
                    );
                }
            }
            /* Move along to start of next line */
            $lineStart += $lineLen + 1;
        }
        if (isset($tmpRow)) {
            /* Tack on the last row */
            $table['row'][] = $tmpRow;
        }
        $parsed = $this->backend->renderTable($table);
        return array('parsed' => $parsed, 'remainderIdx' => $lineStart);
    }

    private function getLineLen(array $textChars, int $position)
    {
        /* Return number of characters in line, or FALSE if the string is depleted */
        for ($i = $position; $i < count($textChars); $i++) {
            if ($textChars[$i] == "\n") {
                return $i - $position;
            }
        }
        return $position < count($textChars) ? count($textChars) - $position : false;
    }

    /**
     * Retrieve columns started in this line of text
     *
     * @param string $token Type of cells we are looking at (th or td)
     * @param string $text Text to parse
     * @param string $colsSoFar Columns which have already been found in this row
     * @return multitype:string parsed and remaining text
     */
    private function parseTableCells(string $token, array $textChars, int $from, array $colsSoFar)
    {
        $tableElement = $this->tableBlock[$token];
        $len = count($textChars);

        $tmpCol = array('arg' => [], 'content' => '', 'token' => $token);
        $argCount = 0;
        $buffer = '';

        /* Loop through each character */
        for ($i = $from; $i < $len; $i++) {
            $hit = false;
            /* We basically detect the start of any inline/lineblock/table elements and, knowing that the inline parser knows how to handle them, throw then wayward */
            $c = $textChars[$i];
            if (isset($this->inlineLookup[$c])) {
                /* There are inline elements which start with this character. Check each one,.. */
                foreach ($this->inlineLookup[$c] as $key => $child) {
                    if (!$hit && $this->tagIsAt($child->startTag, $textChars, $i)) {
                        $hit = true;
                    }
                }
            }
            if ($c == "\n") {
                if ($this->tagIsAt($this->tableStart->startTag, $textChars, $i + 1)) {
                    /* Table is coming up */
                    $hit = true;
                } else {
                    /* LineBlocks like lists and headings */
                    $next = $textChars[$i + 1];
                    foreach ($this->lineBlock as $key => $block) {
                        foreach ($block->startChar as $char) {
                            if (!$hit && $next == $char) {
                                $hit = true;
                                break 2;
                            }
                        }
                    }
                }
            }

            if ($hit) {
                /* Parse whatever it is and return here */
                $start = $i;
                $result = $this->parseInline($textChars, 'td', $start);
                $buffer .= $result['parsed'];
                // TODO was -1 before, seems to work well though
                $i = $result['remainderIdx'];
            }

            if (!$hit && $this->tagIsAt($tableElement->inlinesep, $textChars, $i)) {
                /* Got column separator, so this column is now finished */
                $tmpCol['content'] = $buffer;
                $colsSoFar[] = $tmpCol;

                /* Reset for the next */
                $tmpCol = array('arg' => [], 'content' => '', 'token' => $token);
                $buffer = '';
                $hit = true;
                $i += count($tableElement->inlinesep) - 1;
                $argCount = 0;
            }

            if (!$hit && $argCount < ($tableElement->limit) && $this->tagIsAt($tableElement->argsep, $textChars, $i)) {
                /* Got argument separator. Shift off the last argument */
                $tmpCol['arg'][] = $buffer;
                $buffer = '';
                $hit = true;
                $i += count($tableElement->argsep) - 1;
                $argCount++;
            }

            if (!$hit) {
                $c = $textChars[$i];
                if ($c == "\n") {
                    /* Checking that the next line isn't starting a different element of the table */
                    foreach ($this->tableBlock as $key => $block) {
                        if ($this->tagIsAt($block->lineStart, $textChars, $i + 1)) {
                            /* Next line is more table syntax. bail otu and let something else handle it */
                            break 2;
                        }
                    }
                }
                $buffer .= $c;
            }
        }

        /* Put remaining buffers in the right place */
        $tmpCol['content'] = $buffer;
        $colsSoFar[] = $tmpCol;
        $start = $i + 1;
        return array('col' => $colsSoFar, 'remainderIdx' => $start);
    }

    private function countChar(array $chars, array $text, int $position, int $max = 0)
    {
        $i = 0;
        while ($i < $max && array_search($text[$position + $i], $chars) !== false) {
            $i++;
        }
        return $i;
    }

    private function countCharReverse(array $chars, array $text, int $min, int $position)
    {
        $i = 0;
        while (($position - $i) > $min && array_search($text[$position - $i], $chars) !== false) {
            $i++;
        }
        return $i;
    }

    /**
     * Create a list from what we found in parseLineBlock(), returning all elements.
     */
    private function makeList(array $lines)
    {
        $list = $this->findChildren($lines, 0, -1);
        return $list['child'];
    }

    /**
     * Recursively nests list elements inside eachother, forming a hierachy to traverse when rendering
     */
    private function findChildren(array $lines, $depth, $minKey)
    {
        $children = [];
        $not = [];

        foreach ($lines as $key => $line) {
            /* Loop through for candidates */
            if ($key > $minKey) {
                if ($line['depth'] > $depth) {
                    $children[$key] = $line;
                    unset($lines[$key]);
                } elseif ($line['depth'] <= $depth) {
                    break;
                }
            }
        }

        /* For each child, list its children */
        foreach ($children as $key => $child) {
            if (isset($children[$key])) {
                $result = $this->findChildren($children, $child['depth'], $key);
                $children[$key]['child'] = $result['child'];

                /* We know that all of this list's children are NOT children of this item (directly), so remove them from our records. */
                foreach ($result['child'] as $notkey => $notchild) {
                    unset($children[$notkey]);
                    $not[$notkey] = true;
                }

                /* And same for non-direct children reported above */
                foreach ($result['not'] as $notkey => $foo) {
                    unset($children[$notkey]);
                    $not[$notkey] = true;
                }
            }
        }

        return array('child' => $children, 'not' => $not);
    }

}
