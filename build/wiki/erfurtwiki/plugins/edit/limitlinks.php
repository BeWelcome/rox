<?php
//@FIX: make it an revoverably error, include captcha to allow
//      proceeding the save - else this becomes too cumbersome
/*
  This is an anti-spam plugin, which simply disallows adding more than
  a given number of external links to pages. It will hurt you, if you
  use your Wiki for creation of a link directory, otherwise it is a
  good way to fight bored link spammers (because it makes you a less
  interesting target if they cannot add hundreds of links at once
  anymore).
  Raise or lower the allowed number as you see fit (but 15 or 20 is
  advised as maximum).
*/

define("EWIKI_LIMIT_LINKS", 5);
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_limit_adding_external_links";

$ewiki_t["en"]["LINK_ADDING_LIMITED"] = "Sorry, but adding links to this page is restricted to a certain amount. You reached it, and therefore page saving was cancelled.<br><br>If you're a bored link spammer, this is message is for you:<br>*hehe*, *laugh*<br>Happy Death!";


function ewiki_edit_save_limit_adding_external_links(&$save, &$old) {
   global $ewiki_errmsg;

   #-- count
   preg_match_all('°(http://[^\s*<>"\'\[\]\#]+)°', $old["content"], $old_urls);
   preg_match_all('°(http://[^\s*<>"\'\[\]\#]+)°', $save["content"], $save_urls);
   $added_urls = array_diff($save_urls[1], $old_urls[1]);

   #-- engage trap
   if (count($added_urls) > EWIKI_LIMIT_LINKS) {

      #-- abort saving with an error message
      $save = array();
      $ewiki_errmsg = ewiki_t("LINK_ADDING_LIMITED");
      return(false);
   }
}

?>