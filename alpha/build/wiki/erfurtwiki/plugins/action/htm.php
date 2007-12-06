<?php
/*
   WikiFeatures:SnippetPublishing, this plugin provides the action "htm"
   to allow external sites to 'embed' the bare html content of a page
   using an URL like "http://example.com/ewiki/?id=PageName&action=htm"
*/

$ewiki_plugins["action"]["htm"] = "ewiki_action_htm";
function ewiki_action_htm($id, &$data, $action) {
   die(ewiki_page_view($id, $data, $action, $_full_page=0));
}

?>