<?php

 # this converts the "mediawiki code" to the easier
 # wiki syntax internally



 $ewiki_plugins["format_source"][] = "ewiki_format_emulate_mediawiki";



 function ewiki_format_emulate_mediawiki(&$source) {

    #-- else simple string replacements will do:
    $repl = array(
       '[[' => '[',
       ']]' => ']',
       '{{' => '{',
       '}}' => '}',
       '{|' => '',
       '|}' => '',
       '|-' => '',
       '{' => '',
       '}' => '',
    );

    foreach ($repl as $from => $to) {
       $source = str_replace($from, $to, $source);
    }

 }


?>