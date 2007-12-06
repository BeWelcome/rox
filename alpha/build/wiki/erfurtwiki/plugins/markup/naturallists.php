<?php

/*
   Using this plugin, ordinary plain text written enumarated lists are
   recognized (transformed into Wiki lists internally).

   1. list
   2. list
   3) list
     a. list
     b. list
   4) list
*/


$ewiki_plugins["format_source"][] = "ewiki_format_src_natural_lists";

function ewiki_format_src_natural_lists(&$src) {

   $src = preg_replace(
      "/^(  )*(\d+|[a-zA-Z])[.)]\s/me", 
      ' str_repeat("#", strlen("$1")>>1) . "#" . (0 ? "" : substr("$2",0,1)) . " " ',
      $src
   );
}


?>