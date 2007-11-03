<?php
/*
   With a rel="NOFOLLOW" attribute on hyperlinks <a> tag, you'll prevent
   additional page rank bonus and thus any interest for link spammers to
   target your Wiki (see [doc/LinkSpammers]). This plugin is a bit more
   clueful in only adding this flag to fresh/new links.

   It looks up the (at least) two weeks older version of a page to make
   a decision. That isn't all too slow actually, but you could use the
   dumber variant "plugins/linking/a_nofollow" alternatively.
*/

$ewiki_plugins["fromat_prepare_linking"][] = "ewiki_prepare_linking_find_old_links";
$ewiki_plugins["link_final"][] = "ewiki_linking_nofollow_flag_fresh_urls";


#-- searches links from older page version
function ewiki_prepare_linking_find_old_links(&$src) {
   global $ewiki_config, $ewiki_id, $ewiki_data;
      
   #-- prepare
   $ewiki_config["old_version_links"] = array();
   $max_time = time() - 2*7 * 24*60*60;
   if ($ewiki_data["lastmodified"] <= $max_time) {
      return;
   }
   
   #-- search version which is at least two weeks older
   $version = $ewiki_data["version"] - 1;
   while ($version > 1) {
      $data = ewiki_db::GET($ewiki_id, $version-1);
      if ($data && ($data["lastmodified"] <= $max_time)) {
         break;
      }
   }
   if ($data) {
      $ewiki_config["old_version_links"] = explode("\n", trim($data["refs"]));
   }
}


#-- adds NOFOLLOW attribute to "fresh" urls
function ewiki_linking_nofollow_flag_fresh_urls(&$str, $type, $href, $title, &$states) {
   global $ewiki_config;
   if (strpos($href, "://") && !in_array($href, $ewiki_config["old_version_links"])) {
      $states["xhtml"]["rel"] = "NOFOLLOW , NOPAGERANK , NOCOUNT";
   }
}

?>