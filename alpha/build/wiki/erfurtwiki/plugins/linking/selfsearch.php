<?php

/*
   any SelfLink of a page will trigger a PowerSearch when clicked on
*/

$ewiki_plugins["format_prepare_linking"][] = "ewiki_self_search";

function ewiki_self_search(&$src) {
   global $ewiki_links, $ewiki_id;
   $ewiki_links[$ewiki_id] = ewiki_script("search", $ewiki_id);
}

?>