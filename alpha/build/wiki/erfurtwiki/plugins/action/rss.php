<?php
/*
   provides a rss/atom feed for individual pages
   (RecentChanges/UpdatedPages must do themselves)

   UpdatedPages/RecentChanges with UseMod params:
     - unique=1 every page just once
     - diffs=1 instead of content
     - ddiffs=1 to link to diff/ or qdiff/
*/

$ewiki_plugins["action"]["rss"] = "ewiki_action_rss";
$ewiki_plugins["page"]["RSS"] = "ewiki_action_rss";
$ewiki_config["action_links"]["view"]["rss"] = "RSS/Atom";


function ewiki_action_rss($id, &$data, $action)
{
   $list = array();
   
   #-- recentchanges as rss
   if (strtoupper($id) == "RSS") {
      $res = ewiki_db::GETALL(array("id", "flags", "version", "lastmodified"));
      $sort = array();
      while ($data = $res->get(0, 0x0137, EWIKI_DB_F_TEXT)) {
         $sort[$data["id"]] = $data["lastmodified"];
      }
      arsort($sort);
      $limit = 100;
      foreach ($sort as $id=>$uu) {
         $list[] = ewiki_db::GET($id);
         if ($limit-- < 0) { break; }
      }
   }
   
   #-- history of current page
   else {
      $list[] = $data;
      for ($v=$data["version"]-1; $v>=1; $v--) {
         $d = ewiki_db::GET($id, $v);
         $list[] = $d;
      }
   }

   ewiki_feed($list);
}

?>