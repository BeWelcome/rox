<?php

#  this plugin chains into the url/link generation process and
#  adds <a target="_blank"> for external http:// urls, which will
#  make (most) browsers open the links in a new window



$ewiki_plugins["link_final"][] = "ewiki_link_final_target_blank";


function ewiki_link_final_target_blank(&$str, $type, $href, $title) {

   if (is_array($type) && in_array("url", $type)) {
      $str = str_replace('<a ', '<a target="_blank" ', $str);
   }
}


?>