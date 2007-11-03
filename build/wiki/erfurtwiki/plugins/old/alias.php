<?php

#  Use this plugin to map PageAliases to existing WikiPages.
#  If you add an alias for a page, it's assumed to exist.
#
#  Note: this plugin was absoleted by the "jump.php" plugin, which provides
#  a more convinient feature that implements the idea behing page aliases
#  much better and more intuitively.


$ewiki_plugins["alias"] = array(
   "FrontPage" => "ErfurtWiki",
   "WikiInfo" => "AboutPlugins",
   "PageAlias" => "RealName",
// ...
);


$ewiki_plugins["format_source"] = "ewiki_page_aliases";


function ewiki_page_aliases(&$src) {
   global $ewiki_links, $ewiki_plugins;
   $ewiki_links = array_merge(
      $ewiki_links,
      $ewiki_plugins["alias"]
   );
}

foreach ($ewiki_plugins["alias"] as $page=>$uu) {
   $ewiki_plugins["page"][$page] = "ewiki_page_alias";
}

function ewiki_page_alias($id, $data, $action) {
   global $ewiki_plugins;
   return(ewiki_page($ewiki_plugins["alias"][$id]));
}


?>