<?php

 # this converts the "mediawiki code" to the easier
 # wiki syntax internally

 $ewiki_plugins["format_block"]["mediawiki_image_tag"][] = "ewiki_format_mediawiki_image_tag";
 $ewiki_config["format_block"]["mediawiki_image_tag"] = array("[[Image:", "]]", false, 0x0030);

 $ewiki_plugins["format_source"][] = "ewiki_format_emulate_mediawiki";
 $ewiki_plugins["edit_save"][] = "ewiki_format_emulate_mediawiki_edit";
 $ewiki_plugins["format_line"][] = "ewiki_format_emulate_doublebrackets";
 $ewiki_plugins["format_table"][] = "ewiki_format_emulate_mediawiki_table";

 function ewiki_format_mediawiki_image_tag(&$c, &$in, &$ooo, &$s) {

     $str = str_replace(' ','',$c);
     $l = strpos($str, "|");
     if ($l !== false) {
         $title = substr($str, strripos($str,"|")+1);
         $src = substr($str,$p,$l);
     } else {
         $src = substr($str,$p);
     }
     if (strpos($str, "|thumb|")) {
         $class .= 'thumb ';
     }
     if (strpos($str, "|framed|")) {
         $class = 'framed ';
     }
     $c = '<img src="'.$src.'" '.(isset($title) ? 'alt="'.$title.'"' : $src).' '.(isset($class) ? 'class="'.$class.'"' : '').' />';
 }
 
 function ewiki_format_emulate_doublebrackets(&$o, &$line, &$post) {
     
     // Now let's switch all the rest to simple erfurtwiki style
     $line = str_replace('[[','[',$line);
     $line = str_replace(']]',']',$line);
 }

 function ewiki_format_emulate_mediawiki_table(&$line, &$ooo, &$s) {
     // Erfurtwiki tables are not supported by MediaWiki!
 }

 function ewiki_format_emulate_mediawiki(&$source) {

    #-- else simple string replacements will do:
    $repl = array(
       // '[[' => '[',
       // ']]' => ']',
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
       '~~~~' => '['.PVars::getObj('env')->baseuri.'members/'.$ewiki_author.' '.$ewiki_author.'] - '.date(DATE_RFC822,$data['created']),
    );
    // $data = $old_data;
    foreach ($repl as $from => $to) {
       $data['content'] = str_replace($from, $to, $data['content']);
    }
 }


?>