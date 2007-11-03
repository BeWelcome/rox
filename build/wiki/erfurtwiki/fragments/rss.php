<?php
/*
  This script provides an RSS feed for your Wiki, if invoked from outside.
  It itself makes use of the plugins/lib/feed.php extension, which you
  should rather use from now on. (URL param support was dropped).

  Please define EWIKI_SCRIPT_URL correctly, before using this!
*/

#-- load ewiki
chdir("..");
require("config.php");

#-- load RSS libs
if (!function_defined("ewiki_feed")) {
   include("plugins/lib/feed.php");
}
if (!function_defined("ewiki_action_rss")) {
   include("plugins/action/rss.php");
}

#-- send RSS
$_SERVER["HTTP_ACCEPT"] .= ", application/rss+xml; revision=2.0";  // for the lazy
$data = false;
ewiki_action_rss($id="RSS", $data, $action="rss");

?>