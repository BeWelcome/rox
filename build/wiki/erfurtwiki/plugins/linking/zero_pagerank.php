<?php

/*
   enabling this will filter all BannedLinks through a page rank
   killer (google.com itself or "fragments/zero_pagerank.php")
   - you could use "plugins/edit/spam_deface" alternatively
   - the advantage of _this_ plugin however is, that it always can
     filter banned urls, while the plugins/edit/ version only works
     at edit time (and if the URL was already listed on BannedLinks)
*/

// define("ZERO_PAGERANK", "http://erfurtwiki.sourceforge.net/fragments/zero_pagerank.php?url=");
define("ZERO_PAGERANK", "http://www.google.com/url?sa=D&q=");
define("EWIKI_PAGE_BANNED", "BannedLinks");
$ewiki_config["info_refs_once"] = 1;


$ewiki_plugins["link_url"][] = "ewiki_link_url_zero_pagerank";
function ewiki_link_url_zero_pagerank(&$href, &$title) {
   if (ewiki_banned_link($href)) {
      $href = ZERO_PAGERANK . urlencode($href);
   }
}


function ewiki_banned_link($href) {
   global $ewiki_config;
   
   #-- buffer list of banned urls
   if (!isset($ewiki_config["banned"])) {
      $data = ewiki_db::GET(EWIKI_PAGE_BANNED);
      $ewiki_config["banned"] = trim(strtolower($data["refs"]));
   }

   #-- check for entry
   if ($b = &$ewiki_config["banned"]) {
      $href = strtolower(urldecode($href));
      if (strpos($b, $href) !== false) {
         return(true);
      }
      foreach (explode("\n", $b) as $bad) {
         if (strpos($href, $bad) !== false) {
            return(true);
         }
      }
   }

   #-- advanced
   if ($pf_a = $ewiki_plugins["ban_lookup"]) {
      foreach ($pf_a as $pf) {
         if ($pf($href)) {
            return(true);
         }
      }
   }

   return(false);
}


?>