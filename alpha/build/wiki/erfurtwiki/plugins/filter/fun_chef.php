<?php

/*
   Code modelled after Joes Hess` description of the 'chef' filter from
   the debian text filters package. This one is heavily broken,
   rearranging the rules is required; some other differences are because
   the orig. 'chef' filter gets some rules wrong too.
*/


$ewiki_plugins["view_final"][] = "ewiki_page_filter_chef";


function ewiki_page_filter_chef(&$o) {
   $o = preg_replace('/>([^<>]+)</e',
   '">".stripslashes(strhtml_chef("\\1"))."<"', $o);
}


function strhtml_chef($text) {

   #-- end of word
   $text = preg_replace('/th\b/', 't', $text);
   $text = preg_replace('/en\b/', 'ee', $text);

   #-- simple
   $text = preg_replace('/the/', 'zhe', $text);
   $text = preg_replace('/The/', 'Zhe', $text);

   #-- if not first letter
   $text = preg_replace('/(\w)f/', '\\1ff', $text);
   $text = preg_replace('/\b(\w+?)i(\w+)\b/', '\\1ee\\2', $text);

   #-- u to oo, o to u
   $text = strtr($text, "uo", "ou");
   $text = preg_replace('/o/', 'oo', $text);
   #-- first letter o
   $text = preg_replace('/\bO(\w)/', 'Oo\\2', $text);

   #-- always (o and u rules)
   $text = preg_replace('/au/', 'oo', $text);
   $text = preg_replace('/Au/', 'Oo', $text);

   #-- always (not conflicting)
   $text = preg_replace('/v/', 'f', $text);
   $text = preg_replace('/V/', 'F', $text);
   $text = preg_replace('/w/', 'v', $text);
   $text = preg_replace('/W/', 'V', $text);
   $text = preg_replace('/an/', 'un', $text);
   $text = preg_replace('/An/', 'Un', $text);
// $text = preg_replace('/en/', 'un', $text);   // for bug emulation

   #-- begin of word
   $text = preg_replace('/\be/', 'i', $text);
   $text = preg_replace('/\bE/', 'I', $text);

   #-- not last letter
   $text = preg_replace('/[Aa](\w)/', 'e\\1', $text);

   #-- all following must take into account previous garbaging, and revert
   #   as needed

   #-- simple
   $text = preg_replace('/(tion|teeun)/', 'shun', $text);

   #-- end of word
   $text = preg_replace('/(?<![zZ]h)e\b/', 'e-a', $text);

   $text = preg_replace('/[ou][vw]\b/', 'oo', $text);
	
   $text = preg_replace('/([.!?]\s*\n)/', "\\1<br />\nBork Bork Bork!<br />\n", $text);

   return($text);
}


?>