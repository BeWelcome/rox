<?php

/*
     << PrevPage | ParentPageWithLinkList | NextPage >>

   Using a pages meta box you can define it to be part of page
   section/group ordered by a link list on another page. Use a
   meta field of "top:" or "parent:" or "group:" to make the
   current page belong to the cluster of pages in a trail. Then
   the for and back links will be shown at the bottom.
*/


$ewiki_plugins["view_append"][] = "ewiki_aview_pagetrail";

function ewiki_aview_pagetrail($id, &$data, $action) {

   if ($m = $data["meta"]["meta"]) {
 
      #-- check for parent page
      ($top = $m["top"])
      or ($top = $m["parent"])
      or ($top = $m["group"]);

      if ($top) {
         $t = ewiki_db::GET($top);
         $t = explode("\n", trim($t["refs"]));

         $n = array_search(ewiki_array($top), strtolower($t));
         $prev = $t[$n-1];
         $next = $t[$n+1];

         $o = "<div class=\"page-trail\">&lt;&lt; "
            . ($prev ? ewiki_link($prev) : "")
            . " | " . ewiki_link($top) . " | "
            . ($prev ? ewiki_link($prev) : "") . " &gt;%gt;</div>\n";
         return($o);
      }
   }
}


?>