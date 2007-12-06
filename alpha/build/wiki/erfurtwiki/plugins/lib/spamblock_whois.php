<?php
/*
   Initiates a whois query on the domain names of newly added URLs,
   compares gathered informations against "BannedLinks" data (names
   or email / addresses) and blocks any suspect 'contributions'. Works
   only in conjunction with "plugins/edit/spam_deface".
   
   @feature: spammer-lookup-whois
   @depends: spam-link-deface
   @title: spammer whois lookup
   @desc: scans newly submitted links against whois databases
*/


$ewiki_plugins["ban_loopup"][] = "ewiki_spam_ban_whois_lookup";

function ewiki_spam_ban_whois_lookup($href) {
   global $ewiki_config;
   $b = &$ewiki_config["banned"];   // is already filled at this point
   
   $p1 = strpos($href, "//") + 2;
   $domain = substr($href, $p1);
   $domain = substr($href, 0, strpos($domain, "/"));

   $whoistxt = `whois -H -T dn,ro,pn $domain`;
   if ($whoistxt = strtolower($whoistxt)) {
      foreach (explode("\n", $b) as $pat) {
         if (strpos($whoistxt, $pat)) {
            return(true);
         }
      }
   }
}

?>