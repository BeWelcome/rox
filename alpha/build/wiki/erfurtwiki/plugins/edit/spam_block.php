<?php

/*
   Unlike with ../edit/spam_deface, this plugin allows to really block
   people from adding links. However, you must put the forbidden domain
   names and URL search patterns on the page named "BlockedLinks"
   instead. But this allows you to separate between banned and blocked
   URLs then.
   
   - useful for aggressive link spammers
   - can be used in conjunction with one of the _deface plugins, but
     then must be loaded _before_ any of the other ones
*/

define("EWIKI_PAGE_BLOCKED", "BlockedLinks");
$ewiki_config["info_refs_once"] = 1;  // disable {refs} info/ for old versions

$ewiki_t["en"]["BLOCKED_URL"] = "Sorry boy, but we couldn't accept your submission, because one or more of the contained links is on our blacklist. All newly submitted have been added, ha ha - and depending on the admins preferences a Google complaint has been sent. Now go away.";


$ewiki_plugins["edit_save"][] = "ewiki_edit_save_antispam_urlblock";
function ewiki_edit_save_antispam_urlblock(&$save, &$old_data) {

   global $ewiki_errmsg, $ewiki_id;
   $BLOCK = EWIKI_PAGE_BLOCKED;

   preg_match_all('°(http://[^\s*<>"\'\[\]\#]+)°', $save["content"], $save_urls);
   preg_match_all('°(http://[^\s*<>"\'\[\]\#]+)°', $old_data["content"], $old_urls);

   $added_urls = array_diff($save_urls[1], $old_urls[1]);
   if ($added_urls) {
      foreach ($added_urls as $i=>$url) {
      
         #-- test against BannedLinks, then deface (filter page) URL
         if (ewiki_blocked_link($url, $BLOCK)) {
            $block = true;
            unset($added_urls[$i]);
         }
      }
      $old = $i + 1;
   }

   #-- if matched
   if ($block) {
      #-- add new URLs to our BannedLinks page
      if ($new = count($added_urls)) {
         $content = "";
         foreach ($added_urls as $d) { 
            $d = preg_replace('#^.+//(?:www\.)?#', '', $d);
            $d = preg_replace('#^([^/]+)(/.*)?$*', '$1', $d);
            if ($d) {
               $content .= "\n* [$d] (auto-added by spam attack on [$ewiki_id])";
            }
         }
         if ($content) {
            ewiki_db::APPEND($BLOCK, $content);
         }
         $date = strftime("%c", time());
         ewiki_append_to_page("SpamLog", "\n* spam attack on [$ewiki_id] from $_SERVER[REMOTE_ADDRESS]:$_SERVER[REMOTE_PORT] ($_SERVER[HTTP_USER_AGENT]) happend at $date, around {$new} of the {$old} added URLs were already on BlockedLinks");
      }

      #-- error reporting method for ["edit_save"]
      $save = array();
      $ewiki_errmsg = ewiki_t("BLOCKED_URL");
      return(false);
   }
}



function ewiki_blocked_link($href, $LinkPage=EWIKI_PAGE_BLOCKED) {
   global $ewiki_config, $ewiki_plugins;
   
   if (! ($href = trim(strtolower(urldecode($href)))) ) {
      return;
   }
   
   #-- buffer list of banned urls
   if (!isset($ewiki_config[$LinkPage])) {
      $data = ewiki_db::GET($LinkPage);
      $ewiki_config[$LinkPage] = trim(strtolower($data["refs"]));
   }

   #-- check for entry
   if ($b = &$ewiki_config[$LinkPage]) {
      if (strpos($b, $href) !== false) {            // quick string check
         return(true);
      }
      foreach (explode("\n", $b) as $bad) {         // use as patterns
         if (strlen($bad) && (strpos($href, $bad) !== false)) {
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