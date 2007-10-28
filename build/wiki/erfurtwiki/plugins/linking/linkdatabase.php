<?php

/*
   This plugin implements the LinkDatabase like by UseMod/MeatballWiki
   (which summarizes pages and all links inside of it, each entry line
   separated by a <br />).
*/


$ewiki_plugins["page"]["LinkDatabase"] = "ewiki_linkdatabase";
$ewiki_plugins["handler"][] = "ewiki_linkdatabase";


function ewiki_linkdatabase($id, &$data, $action) {

   if (($_REQUEST["action"]=="links") && ($id=="action=links") && empty($_REQUEST["id"])) {

      $o = array(
         "editlink" => 0,
         "empty" => 0,
         "names" => 1,
         "unique" => 1,  #012     unimplemented
         "sort" => 1,
         "exists" => 2,
         "url" => 0,     #012
         "inter" => 0,   #012
         "search" => "",
      );
      $o = array_merge($o, $_REQUEST);

      #-- first read in all pages
      $res = ewiki_db::GETALL(array("flags", "version", "refs"));
      $all = array();
      while ($row = $res->get()) {
         if (($row["version"]&EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
            $refs = trim($row["refs"]);
            if (!$o["empty"] && empty($refs)) {
               continue;
            }
            $all[$row["id"]] = $refs;
         }
      }

      #-- sort
      ksort($all);

      #-- output
      $out = "";
      foreach ($all as $i=>$refs) {
         $refs = explode("\n", $refs);
         if ($refs) {
            $refs = ewiki_db::FIND($refs);
         }
         foreach ($refs as $ri=>$rv) {
            if ($o["exists"]!= 2) {
               if ($rv != $o["exists"]) {
                  unset($refs[$ri]);
               }
            }
            if ($o["url"] != 1) {
               if ($o["url"] XOR strpos($ri, "://")) {
                  unset($refs[$ri]);
               }
            }
            if ($o["inter"] != 1) {
               if ($o["inter"] XOR strpos($ri, ":") && !strpos($ri, "://")) {
                  unset($refs[$ri]);
               }
            }
            if ($o["search"] && (strpos($ri, $o["search"])===false)) {
               unset($refs[$ri]);
            }
         }
         if (!$o["empty"] && empty($refs)) {
            continue;
         }
         if ($o["sort"]) {
            asort($refs);
         }
         $out .= "<a href=\"" . ewiki_script("", $i) . "\">$i</a>  ";
         foreach ($refs as $i=>$rv) {
            $title = $o[names] ? $i : " ";
            if ($rv) {
               $out .= " <a href=\"" . ewiki_script("", $i) . "\">$title</a>";
            }
            else {
               $out .= " $title";
               if ($o["editlink"]) {
                  $out .= "<a href=\"" . ewiki_script("", $i) . "\">?</a>";
               }
            }
         }
         $out .= "\n";
      }
      return("\n<pre>\n\n\n\n$out</pre>\n");
   }
   elseif ($id == "LinkDatabase") {
      $url = "url:?";
#      $url = "ErfurtWiki:";
      return ewiki_format(<<<EOT
!!! LinkDatabase

The LinkDatabase provides a list of all internal pages together with the
links outgoing from each.

* [{$url}action=links] list pages and links, using default options
* [{$url}action=links&editlink=1] add the "?" link for not existent pages
* [{$url}action=links&empty=1] allowes listing of pages without forward links
* [{$url}action=links&names=0] no link title, just the <a href="...">
* [{$url}action=links&sort=0] order pages and their links
* [{$url}action=links&exists=0] only links to not existing pages
* [{$url}action=links&exists=1] only existing page links
* [{$url}action=links&url=1] add URLs
* [{$url}action=links&url=2] __only__ URLs
* [{$url}action=links&inter=1] add ~InterWiki:Links
* [{$url}action=links&inter=2] __only__ ~InterWiki:Links
* [{$url}action=links&search=google] apply search pattern to page links

handy combinations
* [{$url}action=links&inter=1&editlink=1&empty=1&url=1&sort=0&names=1] show all links
* [{$url}action=links&search=sourceforge&url=2] only URL links to sourceforge
* [{$url}action=links&sort=0&exists=0] unsorted missing pages

Only [{$url}action=links&unique=0] has not been implemented.

EOT
      );
   }
}


?>