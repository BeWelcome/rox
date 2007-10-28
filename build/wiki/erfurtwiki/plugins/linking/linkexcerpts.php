<?php

/*
   Fancyfication of page links with an <a href="..." title="..."> page
   excerpt. This however slows down rendering a lot (pre-fetching all
   associated pages is required) - eventually one could write another
   SQL-only version.
*/


$ewiki_plugins["handler"][] = "ewiki_handler_fancy_linkexcerpts";
$ewiki_plugins["link_final"][] = "ewiki_ahref_fancy_linkexcerpts";


function ewiki_handler_fancy_linkexcerpts($id, &$data, $action) {
   global $ewiki_fancy_linkexcerpts;
   if ($action == "view") {
      $ewiki_fancy_linkexcerpts = array();
      foreach (explode("\n", trim($data["refs"])) as $link) {
         $row = ewiki_db::GET($link);
         if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
            $text = trim(substr($row["content"], 0, 160));
            $text = substr($text, 0, strrpos($text, " "));
            $text = strtr($text, "\t\r\n", "   ");
            $text = htmlentities($text);
            $text = wordwrap($text, 40, "&#10;", 0);
            $ewiki_fancy_linkexcerpts[strtolower($link)] = $text;
         }
      }

   }
}


function ewiki_ahref_fancy_linkexcerpts(&$str, &$type, &$href, &$title) {
   global $ewiki_fancy_linkexcerpts; 
   if ($text = $ewiki_fancy_linkexcerpts[strtolower($href)]) {
      $str = str_replace('<a ', '<a title="'.$text.'"', $str);
   }
}


?>