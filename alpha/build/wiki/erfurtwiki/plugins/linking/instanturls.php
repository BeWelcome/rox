<?php

/*
   Using this plugin allows to have a page, which associates short
   page titles to URLs. Whenever a page title occours on another page,
   it will link directly to the given URL.

   A definition page, can associate titles to URLs using either a
   table or a definition list:

      |Remote site|  http://www.remote.org/|
      |Second thing| http://www.example.com/|
   or:
      :Partners: http://google.com/

   So if you write on another page things like [Remote site], it will
   directly link to the URL you defined somewhere else.
*/

$ewiki_config["instant_url_pages"][] = "InstantURLs";
//$ewiki_config["instant_url_pages"][] = "MoreLinks";


$ewiki_plugins["format_prepare_linking"][] = "ewiki_linking_instanturls";
function ewiki_linking_instanturls(&$src) {

   global $ewiki_links, $ewiki_config;

   #-- get list of URL abbreviations
   if (empty($ewiki_config["instant"])) {
      ewiki_get_instanturls();
   }

   #-- scan for non-existent pages
   foreach ($ewiki_links as $id=>$is) {
      if (!$is) {

         ($url = $ewiki_config["instant"][$id])
         or
         ($url = $ewiki_config["interwiki"][$id])
         ;

         #-- use URL if defined
         if ($url) {
            $ewiki_links[$id] = $url;
         }
   }  }
}


function ewiki_get_instanturls() {
   global $ewiki_config;

   $ewiki_config["instant"] = array();
   $DL = '[:|]([^:|]+)[:|]([^|]+)';

   #-- walk through URL definition pages
   foreach ($ewiki_config["instant_url_pages"] as $id) {

      #-- fetch content
      $data = ewiki_db::GET($id);
      if ($data) {
         preg_match_all("/^$DL/m", $data["content"], $uu);
         if ($uu) {
            foreach ($uu[1] as $i=>$name) {
               $ewiki_config["instant"][trim($name)] = strtok(trim($uu[2][$i]), " ");
            }
      }  }
   }
}

?>