<?php

/*
   Ripped from the debian text filters package. This filter was originally
   written by Joey Hess. While this wasn't mentioned clearly I would assume
   that these two lines were released under the GNU GPL (so now is this
   file, see the GPL.txt file).
   (this is mostly reverse-engineered, the perl stuff made no sense to me
   at all)
   This implementation uses "J" to replace "r", to make it distinguishable
   from the transited "f".
   Good fonts to use with this plugin are sans-serif ones like Arial
   or Verdana.
*/


#-- glue
$ewiki_plugins["view_final"][] = "ewiki_page_upsidedown";
//$ewiki_plugins["handler"][] = "ewiki_page_view_preconvert_upsidedown";


#-- reverses all lines of text
function ewiki_page_view_preconvert_upsidedown(&$id, &$data, &$action) {
   if ($action == "view") {
      $data["content"] = implode("\n", array_reverse(explode("\n", $data["content"])));
   }
}


#-- sends all text pieces trough the filter function below
function ewiki_page_upsidedown(&$o) {
   $o = preg_replace('/>([^<>]+)</e',
   '">".htmlentities((str_upsidedown(stripslashes("\\1"))))."<"', $o);
}


#-- this does the string transformation for plain ASCII text
function str_upsidedown($text) {

   $text = explode("\n", $text);
   foreach ($text as $i=>$line) {

      $line = strrev($line);
      $line = str_replace('"', "''", $line);
      $line = strtolower($line);

      $line = strtr($line,
         "abcdefghijklmnopqrstuvwxyz 123456789 ,!?¯_ []{}<> .''  ",
         "eq)paj6y!fk7wuodbJsfn^mxhz l2Eh59L86 `i¿_¯ ][}{>< ',,  "
      );

      $line = str_replace("k", ">|", $line);
      $text[$i] = $line;

   }
   $text = implode("\n", $text);

   return($text);
}


#-- reversal
function decode_upsidedown($text) {
   $text = explode("\n", $text);
   foreach ($text as $i=>$line) {
      $line = str_replace(">|", "k", $line);
      $line = strtr($line,
         "eq)paj6y!fk7wuodbJsfn^mxhz l2E59L8 `i%_¯ ][}{>< ',,  ",
         "abcdefghijklmnopqrstuvwxyz 1235678 ,!?¯_ []{}<> .''  "
      );
      $line = str_replace("''", '"', $line);
      $line = strrev($line);
      $text[$i] = $line;
   }
   $text = implode("\n", $text);
   return($text);
}


?>