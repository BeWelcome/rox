<?php

/*
   Activating this plugin will lead to automagical linking of words in a
   page, if a corresponding wiki page exists. Tilde or exclamation mark
   escaping of autolinked words won't be possible.

   This functionality is not fully automatic and requires manuual activation
   of the helper code in "spages/Admin/PrepareAutolinking" occasionally
   (which will generate an internal database cache entry). There is commented
   out code herein, which you could enable to go without the cache thing (but
   beware that this is slower!).
   You could also just add an array() listing of words, which you would like
   to get automatically linked.

   Also you could choose to use the second word replacement code fragment
   instead of the regex stuff.
*/

define("EWIKI_AUTOLINKING_CACHE", "system/tmp/autolinking");

$ewiki_plugins["format_source"][] = "ewiki_autolinking";


function ewiki_autolinking(&$wiki) {

   #-- get cached list of auto-link pages
   $cache = ewiki_db::GET(EWIKI_AUTOLINKING_CACHE);
   if ($cache) {
      $words = explode("\n", trim($cache["refs"]));
   }

/*
   #-- enable this to go without the cache thingi (much slower this way!)
   if (empty($words)) {
      $words = array();
      $result = ewiki_db::GETALL(array("id", "flags"));
      while ($row = $result->get()) {
         if ((EWIKI_DB_F_TEXT == ($row["flags"] && EWIKI_DB_F_TYPE))
         && !strpos($row["id"], " ") && preg_match('/^.['.EWIKI_CHARS_L.']+$/', $row["id"]) )
         {
            $words[] = $row["id"];
         }
      }
   }
*/


/*
   #-- or add your list of words which should automatically get linked as pages
   $words = array_merge((array)$words, array(
      "start",
      "office",
      "...", 
   ));
*/


/*
   #-- actually replace these words with square brackets around them
   if ($words) {

      if (count($words) >= 50) {
         $find_regex = "/\b(\w{3,})\b/e";
         $regex_replace = "(in_array('\\1', \$words) ? '[\\1]' : '\\1')";
      }
      else {
         $find_regex = "/\b(" . (implode("|", $words)) . ")\b/";
         $regex_replace = "[\\1]";
      }

      $wiki = preg_replace($find_regex, $regex_replace, $wiki);
   }
*/ 


   #-- faster(???) simple string functions based replacing;
   #   (this is not exactly matching, but still better than the above
   #   regex approach)
   if ($words) {
      foreach ($words as $word) {

         $max = strlen($wiki);
         $l = 0;
         while ((($l = strpos($wiki, $word, $l)) !== false) && ($l < $max) ) {

            #-- check if the word stands alone (no letters before or after)
            $r = $l + strlen($word);
            if (  ($l >= 1) && ($c = ord($wiki[$l-1])) && ($c >= 64) && ($c <= 122)
              || ($max>=$r) && ($c = ord($wiki[$r])) && ($c >= 64) && ($c <= 122) )
            {
               $l = $r + 3;
               continue;
            }

            #-- guess if the word is enclosed in square brackets
            if ( (($close_sq = strpos($wiki, "]", $l)) === false) 
            || (($open_sq = strpos($wiki, "[", $l)) !== false) && ($open_sq <= $close_sq)
            || (($line_end = strpos($wiki, "\n", $l)) !== false) && ($line_end <= $close_sq))
            {
               $wiki = substr($wiki, 0, $l)
                     . "[$word]"
                     . substr($wiki, $l + strlen($word));
            }

            $l = $r + 3;  // skip, so we won't end up in a loop
         }
      }
   }


}


?>