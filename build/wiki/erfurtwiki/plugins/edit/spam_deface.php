<?php

/*
   The page "BannedLinks" can hold URLs (or domain name / link patterns,
   if put into square brackets) which will be filtered from the Wiki
   everytime someone tries to put them on a page.
   Filtering uses Google or a special redirection page to decrease
   search engine ranking.
   
   - use this plugin if you encounter consecutive spam injection; it
     is the preferred/recommended method
   - plugins/linking/zero_pagerank.php is however more accurate and
     up-to-date with banned URLs, as it operates at rendering time
     (but therefore was also slower)
*/

// define("ZERO_PAGERANK", "http://www.google.com/url?sa=D&q=");
define("ZERO_PAGERANK", "http://erfurtwiki.sourceforge.net/fragments/zero_pagerank.php?url=");
define("EWIKI_PAGE_BANNED", "BannedLinks");
$ewiki_config["info_refs_once"] = 1;  // disable {refs} info/ for old versions


$ewiki_plugins["edit_save"][] = "ewiki_edit_save_antispam_urldeface";
function ewiki_edit_save_antispam_urldeface(&$save, &$old) {

   preg_match_all('°(http://[^\s*<>"\'\[\]\#]+)°', $old["content"], $old_urls);
   preg_match_all('°(http://[^\s*<>"\'\[\]\#]+)°', $save["content"], $save_urls);

   $added_urls = array_diff($save_urls[1], $old_urls[1]);
   if ($added_urls) {
      foreach ($added_urls as $url) {
      
         #-- test against BannedLinks, then deface (filter page) URL
         if (ewiki_banned_link($url)) {
            $save["content"] = str_replace($url, ZERO_PAGERANK.urlencode($url), $save["content"]);
         }
      }
   }
}


function ewiki_banned_link($href) {
   global $ewiki_config, $ewiki_plugins;
   
   #-- buffer list of banned urls
   if (!isset($ewiki_config["banned"])) {
      $data = ewiki_db::GET(EWIKI_PAGE_BANNED);
      $ewiki_config["banned"] = trim(strtolower($data["refs"]));
   }

   #-- check for entry
   if ($b = &$ewiki_config["banned"]) {
      $href = strtolower(urldecode($href));
      if (strpos($b, $href) !== false) {            // quick string check
         return(true);
      }
      foreach (explode("\n", $b) as $bad) {         // use as patterns
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