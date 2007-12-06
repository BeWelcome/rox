<?php

/*
   any SelfLink on the current page will be redirected to MetaWiki:
*/

$ewiki_plugins["format_prepare_linking"][] = "ewiki_self_metawiki";

function ewiki_self_metawiki(&$src) {
   global $ewiki_links, $ewiki_id;
   $ewiki_links[$ewiki_id] = "http://sunir.org/apps/meta.pl?".urlencode($ewiki_id);
}

?>