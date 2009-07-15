<?php

// we're using empty tags to compact the tests: under real circumstances
// there would be contents in them

class HTMLPurifier_ChildDef_TableTest extends HTMLPurifier_ChildDefHarness
{

    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_ChildDef_Table();
    }

    function testEmptyInput() {
        $this->assertResult('', false);
    }

    function testSingleRow() {
        $this->assertResult('<tr />');
    }

    function testComplexContents() {
        $this->assertResult('<caption /><col /><thead /><tfoot /><tbody>'.
            '<tr><td>asdf</td></tr></tbody>');
        $this->assertResult('<col /><col /><col /><tr />');
    }

    function testReorderContents() {
        $this->assertResult(
          '<col /><colgroup /><tbody /><tfoot /><thead /><tr>1</tr><caption /><tr />',
          '<caption /><col /><colgroup /><thead /><tfoot /><tbody /><tr>1</tr><tr />');
    }

    function testDuplicateProcessing() {
        $this->assertResult(
          '<caption>1</caption><caption /><tbody /><tbody /><tfoot>1</tfoot><tfoot />',
          '<caption>1</caption><tfoot>1</tfoot><tbody /><tbody /><tbody />'
        );
    }

    function testRemoveText() {
        $this->assertResult('foo', false);
    }

    function testStickyWhitespaceOnTr() {
        $this->config->set('Output.Newline', "\n");
        $this->assertResult("\n   <tr />\n  <tr />\n ");
    }

    function testStickyWhitespaceOnTSection() {
        $this->config->set('Output.Newline', "\n");
        $this->assertResult(
          "\n\t<tbody />\n\t\t<tfoot />\n\t\t\t",
          "\n\t\t<tfoot />\n\t<tbody />\n\t\t\t"
        );

    }

}

// vim: et sw=4 sts=4
