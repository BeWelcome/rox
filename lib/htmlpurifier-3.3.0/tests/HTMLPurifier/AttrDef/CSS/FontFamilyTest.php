<?php

class HTMLPurifier_AttrDef_CSS_FontFamilyTest extends HTMLPurifier_AttrDefHarness
{

    function test() {

        $this->def = new HTMLPurifier_AttrDef_CSS_FontFamily();

        $this->assertDef('Gill, Helvetica, sans-serif');
        $this->assertDef('\'Times New Roman\', serif');
        $this->assertDef('"Times New Roman"', "'Times New Roman'");
        $this->assertDef('01234');
        $this->assertDef(',', false);
        $this->assertDef('Times New Roman, serif', '\'Times New Roman\', serif');
        $this->assertDef($d = "'John\\'s Font'");
        $this->assertDef("John's Font", $d);
        $this->assertDef($d = "'\xE5\xAE\x8B\xE4\xBD\x93'");
        $this->assertDef("\xE5\xAE\x8B\xE4\xBD\x93", $d);
        $this->assertDef("'\\','f'", "'\\\\', f");
        $this->assertDef("'\\01'", "''");
        $this->assertDef("'\\20'", "' '");
        $this->assertDef("\\0020", "'\\\\0020'");
        $this->assertDef("'\\000045'", "E");
        $this->assertDef("','", false);
        $this->assertDef("',' foobar','", "' foobar'");
        $this->assertDef("'\\27'", "'\''");
        $this->assertDef('"\\22"', "'\"'");
        $this->assertDef('"\\""', "'\"'");
        $this->assertDef('"\'"', "'\\''");
        $this->assertDef("'\\000045a'", "Ea");
        $this->assertDef("'\\00045 a'", "Ea");
        $this->assertDef("'\\00045  a'", "'E a'");
        $this->assertDef("'\\\nf'", "f");
    }

}

// vim: et sw=4 sts=4
