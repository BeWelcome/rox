<?php
/*
   The [http://gmpg.org/xfn/] XHTML Friends Network spec leverages HTML4
   link syntax (the rel= attribute) to allow connecting persons (homepages)
   by each other using relationship meta data notes. In Wiki you would
   just prefix links from your to another ones user page, like for example
   - friend:met:SomeOne
   - co-resident:OtherPerson
   
   You should then also add following to yoursite, to denote the use of
   XFN meta data:
      <head profile="http://gmpg.org/xfn/1">

   This plugin allows a few more technical (non-person-related) page meta
   link flags, like "unlink" to defend against untruthful backlinks.
*/


$ewiki_config["xhtml_rel"] = array(
   "friend", "acquaintance",
   "met",
   "child", "parent", "sibling", "spouse",
   "co-resident", "neighbor",
   "co-worker", "colleague",
   "muse", "crush", "date", "sweatheart",
 #-- not XFN --
   "unlink",                      // anti-link (against XFN spam)
   "near", "contains", "partof",  // for things
);


$ewiki_plugins["interxhtml"][] = "ewiki_interxhtml_xfn";

function ewiki_interxhtml_xfn($prefix, &$page, &$s) {
   global $ewiki_config;

   if (in_array($ewiki_config["xhtml_rel"], $prefix)) {
      $s["xhtml"]["rel"] = trim($s["xhtml"]["rel"] . " $prefix");
   }
}


?>