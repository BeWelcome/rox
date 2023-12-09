<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * TimestampDiffFunction ::= "TIMESTAMPDIFF" "(" ArithmeticPrimary "," ArithmeticPrimary "," ArithmeticPrimary ")".
 */
class TimestampDiff extends FunctionNode
{
    /**
     * @var Node|null
     */
    public $unit = null;

    /**
     * @var Node|null
     */
    public $firstDatetimeExpression = null;

    /**
     * @var Node|null
     */
    public $secondDatetimeExpression = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->unit = $lexer->token['value'];
        $parser->match(Lexer::T_COMMA);
        $this->firstDatetimeExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondDatetimeExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'TIMESTAMPDIFF(' .
            $this->unit . ', ' .
            $this->firstDatetimeExpression->dispatch($sqlWalker) . ', ' .
            $this->secondDatetimeExpression->dispatch($sqlWalker) .
            ')';
    }
}
