<?php

/*
   Any occourence of the current page name will point to the links/
   page (BackLinks) for itself.   
*/

$ewiki_plugins["format_prepare_linking"][] = "ewiki_self_backlink";

function ewiki_self_backlink(&$src) {
   global $ewiki_links, $ewiki_id;
   $ewiki_links[$ewiki_id] = ewiki_script("links", $ewiki_id);
}

?>