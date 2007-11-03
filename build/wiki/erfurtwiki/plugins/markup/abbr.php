<?php

/*
   The pages "Acronyms" and "Abbreviations" are read in by this plugin,
   and its contents (a table or definition list) are used to replace
   all occourences of the noticed words with <abbr> and <acronym> tags
   on other pages (case-sensitive).
*/

$ewiki_config["acronym"][] = "Acronyms";
$ewiki_config["abbr"][] = "Abbreviations";


$ewiki_plugins["format_final"][] = "ewiki_acronyms";
//(or even)  $ewiki_plugins["page_final"][] = "ewiki_acronyms";


function ewiki_acronyms(&$html) {

   global $ewiki_config;

   foreach (array("acronym", "abbr") as $tag) {

      #-- read in data pages
      $list = array();
      foreach ($ewiki_config[$tag] as $id) {
         $data = ewiki_db::GET($id);
         preg_match_all('/^[|:]\s*(\w+)\s*[|:]\s*(.+?)[|:\s]*$/m', $data["content"], $uu);
         foreach ($uu[1] as $i=>$str) {
            $list[$str] = htmlentities($uu[2][$i]);
         }
      }

      #-- add html tags to current page
      if ($list && ($search = implode("|", array_keys($list)))) {
         $html .= "<";
         $html = preg_replace(
            "/($search)([^>]*<)/mse",
            "'<$tag title=\"'.\$list['$1'].'\">$1</$tag>' . stripslashes('$2')",
            $html
         );
         $html = rtrim($html, "<");
      }
   }

}


?>