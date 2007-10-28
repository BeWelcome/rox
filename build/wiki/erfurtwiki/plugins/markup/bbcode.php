<?php

 # this converts the "bulletin board code" to the easier
 # wiki syntax internally



 $ewiki_plugins["format_source"][] = "ewiki_format_emulate_bbcode";



 function ewiki_format_emulate_bbcode(&$source) {

    #-- for super-correct translation, we'll need a regex:
    $source = preg_replace('/\[(link|url|img|email)[:=]([^\]\s]+)]/', '[$1|', $source);

    #-- else simple string replacements will do:
    $repl = array(
       '[link]' => '[',
       '[/link]' => ']',
       '[url]' => '[',
       '[/url]' => ']',
       '[img]' => '[',
       '[/img]' => ']',
       '[email]' => '[',
       '[/email]' => ']',
       '[b]' => '__',
       '[/b]' => '__',
       '[i]' => "''",
       '[/i]' => "''",
       '[u]' => "_",
       '[/u]' => "_",
       '[list' => '<!-- [list',
       '[/list]' => '',
       '[*]' => '*',
    );

    foreach ($repl as $from => $to) {
       $source = str_replace($from, $to, $source);
    }

 }


?>