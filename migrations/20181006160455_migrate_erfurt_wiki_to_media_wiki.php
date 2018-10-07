<?php


use Rox\Tools\RoxMigration;

class MigrateErfurtWikiToMediaWiki extends RoxMigration
{
    const ERFURT_WIKI_TEXT = [
        '/\[jump:([^]]+)\]/',           //[jump:...]
        '/<\?plugin *settitle(.*)\?>/i', //sort of a heading 1
        '/^    *([^ ])/',               //indented paragraphs (we always used 4 spaces but also [tab] is allowed
        '/%%%/',                        //newline
        '/([^!~=|[])(\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b):(\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b)(([^]|#])|$)/',
        //CamelCase InterWiki link
        '/([^-!~=|>&[])(\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b)(([^]|#>])|$)/', //CamelCase, dont change if CamelCase is in InternalLink
        '/([^!~]|^)\[([^] |[]+)\]/',    //internal link
        '/\[([^]|[]+)\|([^]|[]+)\]/',   //external links and links with |
        '/\["([^"]+)" ([^ ]+)\]/',      //Ewiki ["..." ...] style links ([... "..."] not recognized)
        '/\[\[([^ :]+):([^]\/@]+)\]\]/', //InterWiki link (the /@ tries to exclude http:// and mailto:)
        '/\[\[(([^] |[]+)\.(png|jpe?g|gif))\]\]/', //image link (only some)
        '/<pre>/',                      //pre open
        '/<\/pre>/',                    //pre close
        '/<nowiki>/',                      //pre open
        '/<\/nowiki>/',                    //pre close
        '/^\* /',                       //lists 1
        '/^\*\* /',                     //lists 2
        '/^\*\*\* /',                   //lists 3
        '/^# /',                        //ordered lists 1
        '/^## /',                       //ordered lists 2
        '/^### /',                      //ordered lists 3
        '/^!{3} ?(.*)$/',               //heading 1
        '/^!{2} ?(.*)$/',               //heading 2
        '/^!{1} ?(.*)$/',               //heading 3
        '/__([^_]+)__/',                //bold 1
        '/\*\*([^*]+)\*\*/',            //bold 2
        '/\'\'([^\']+)\'\'/',           //italic (emphasize)
        '/==(([^= ][^=]+)|[^=])==/',    //monospaced (also taking care of ==X==)
        '/<tt>(.+)<\/tt>/',             //teletype
        '/##([^#]+)##/',                //big text
        '/µµ([^µ]+)µµ/',                //small text
        '/[!~](\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b)/', //~CamelCase + !CamelCase
        '/[!~](\[[^][]+\])/',           //~[text] + !text (just remove ~ and !)
        '/<cc>([A-Z]+[a-z]+[A-Z][A-Za-z>]*)<\/cc>/', //CamelCase, dont change if CamelCase is in InternalLink
        '/^(=+ .*)\[\[(.*)\]\](.* =+)$/',   //remove links in headlines
        '/<([-A-Za-z0-9+_.]+@[-A-Za-z0-9_]+\.[-A-Za-z0-9_.]+[A-Za-z])>/', //<email> addresses
        '/([^<:!~]|^)(\b[-A-Za-z0-9+_.]+@[-A-Za-z0-9_]+\.[-A-Za-z0-9_.]+[A-Za-z]\b)([^>]|$)/', //email addresses
        '/^keywords: /',                //misc1
        '/\[\[ManPages>/',              //misc2
        '/\[\[WikiPedia>/',             //misc3
        '/\[\[FooBarWiki>/'             //misc4
    ];

    const WIKIMEDIA_TEXT = [
        'Please go to [${1}]',          //[jump:...]
        '====== ${1} ======',           //heading 1 (from plugin settitle)
        '> ${1}',                       //indented paragraphs
        '\\\\\\ ',                      //newline
        '${1}<cc>${2}>${3}</cc>${4}',   //CamelCase InterWiki link
        '${1}<cc>${2}</cc>${3}',        //CamelCase (preparation, see below for finish)
        '${1}[[${2}]]',                 //internal link
        '[[${2}|${1}]]',                //external link and links with |
        '[[${2}|${1}]]',                //Ewiki ["..." ...] style links
        '[[${1}>${2}]]',                //InterWiki link
        '{{${1}}}',                     //images link
        '<code>',                       //(<pre>) code open
        '</code>',                     //(</pre>)code close - remove space between < and /, it is included for viewing in dokuwiki
        '<code>',                       //(<pre>) code open
        '</code>',                     //(</pre>)code close - remove space between < and /, it is included for viewing in dokuwiki
        '* ',                           //lists 1 - no changes
        '** ',                          //lists 2 - no changes
        '*** ',                         //lists 3 - no changes
        '# ',                           //ordered lists 1 - no changes
        '## ',                          //ordered lists 2 - no changes
        '### ',                         //ordered lists 3 - no changes
        '= ${1} =',                     //heading 1
        '== ${1} ==',                   //heading 2
        '=== ${1} ===',                 //heading 3
        '\'\'\'${1}\'\'\'',             //bold 1
        '\'\'\'${1}\'\'\'',             //bold 2
        '\'\'${1}\'\'',                 //italic (emphasize)
        '    ${1}',                     //monospaced
        '    ${1}',                     //teletype
        '**${1}**',                     //big text -- no markup in dokuwiki
        '${1}',                         //small text -- no markup in dokuwiki
        '${1}',                         //~CamelCase + !CamelCase
        '${1}',                         //~[text] + !text (just remove ~ and !)
        '[[${1}]]',                     //CamelCase, finish <cc>CamelCase</cc>
        '${1}${2}${3}',                 //remove links in headlines
        '${1}',                         //<email> addresses
        '${1}<${2}>${3}',               //email addresses
        '**keywords:** ',               //misc1
        '[[man>',                       //misc2
        '[[wp>',                        //misc3
        '[[FooBarWiki>'                 //misc4
    ];

    public function up()
    {
        $this->update(self::ERFURT_WIKI_TEXT, self::WIKIMEDIA_TEXT);
    }

    public function down()
    {
        $this->update(self::WIKIMEDIA_TEXT, self::ERFURT_WIKI_TEXT);
    }

    /**
     * @param array $find
     * @param array $replace
     */
    private function update($find, $replace)
    {
        $updatedRows = [];
        $wiki = $this->table('ewiki');
        $builder = $this->getQueryBuilder();
        $statement = $builder->select('*')->from('ewiki')->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $row['content'] = $this->replaceWikiText($row['content'], $find , $replace);
            $updatedRows[] = $row;
        }
        $wiki->truncate();
        array_walk($updatedRows, function($value) use ($wiki) {
            $wiki->insert($value);
            $wiki->save();
        });
    }

    private function replaceWikiText($text, $find, $replace)
    {
        $ret = '';
        $lines = explode("\n", $text);
        foreach ($lines As $line) {
            $line = preg_replace($find, $replace, $line);

            $ret = $ret . $line . "\n";
        }
        return $ret;
    }
}