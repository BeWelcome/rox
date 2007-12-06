<?php
/*
   <a href="..." rel="NOFOLLOW"> prevents additional page rank bonus
   for linked pages. This is a countermeasure against link spam. (See
   doc/LinkSpammers)
   
   This is a dumb plugin, because it adds this to ALL links. Use the
   "new_nofollow" version which appends this flag only to FRESH links.
*/

$ewiki_plugins["link_final"][] = "ewiki_linking_all_urls_nofollow";

function ewiki_linking_all_urls_nofollow(&$str, $type, $href, $title, &$states) {
   if (strpos($href, "://")) {
      $states["xhtml"]["rel"] = "NOFOLLOW";
   }
}

?>