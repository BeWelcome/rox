<?php

 # this converts the "mediawiki code" to the easier
 # wiki syntax internally



 $ewiki_plugins["format_source"][] = "ewiki_format_emulate_mediawiki";
 $ewiki_plugins["edit_save"][] = "ewiki_format_emulate_mediawiki_edit";
 

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
 
 function ewiki_format_emulate_mediawiki_edit(&$data, &$old_data) {
    global $ewiki_author;
    $repl = array(
       '~~~~' => '['.PVars::getObj('env')->baseuri.'members/'.$ewiki_author.' | '.$ewiki_author.'] - '.date(DATE_RFC822,$data['created']),
    );
    // $data = $old_data;
    foreach ($repl as $from => $to) {
       $data['content'] = str_replace($from, $to, $data['content']);
    }
 }


?>