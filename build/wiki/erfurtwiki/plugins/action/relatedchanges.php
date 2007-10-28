<?php

/*
   relchanges/WikiPage will look for changed pages that link back
   to the current one (tracks link additions and to certain degree
   also removals)
*/

$ewiki_plugins["action_always"]["relchanges"] = "ewiki_action_related_changes";
$ewiki_config["action_links"]["view"]["relchanges"] = "RelatedChanges";


function ewiki_action_related_changes($id, &$data, $action) {

return "unfinished";
}

?>