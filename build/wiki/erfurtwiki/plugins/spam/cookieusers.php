<?php
/*
   AntiSpam-plugins can safely be disabled for real users, which we can
   easily sort out, because of cookies which bots and zombie machines
   won't usually gain (ProtectedEmail and other Captcha-pass cookies).
   This is another anti-trigger plugin.
*/

define("EWIKI_UP_NOBOTCHK", "disable_bot_checks");

#-- detect already set cookies
$ewiki_plugins["init"][] = "ewiki_anti_trigger_bots_dont_use_cookies";
function ewiki_anti_trigger_bots_dont_use_cookies() {
   global $ewiki_no_bot, $ewiki_config;
   if (defined("EWIKI_UP_NOSPAMBOT") && isset($_COOKIES[EWIKI_UP_NOSPAMBOT])
   or defined("EWIKI_UP_NOBOTCHK") && isset($_COOKIES[EWIKI_UP_NOBOTCHK])) {
      $ewiki_no_bot = 1;
   }
   else {
      $ewiki_config["bot_disable"] = 1;   // send a cookie later...
   }
}

#-- set cookie for real users - after any captcha was solved e.g.
$ewiki_plugins["page_final"][] = "ewiki_page_final_cookieusers_set";
function ewiki_page_final_cookieusers_set() {
   global $ewiki_no_bot, $ewiki_config;
   if ($ewiki_no_bot && $ewiki_config["bot_disable"]) {
      setcookie(EWIKI_UP_NOBOTCHK, "1", time()+21*24*3600, "/");
   }
}

?>