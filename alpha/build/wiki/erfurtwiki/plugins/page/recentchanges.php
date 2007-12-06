<?php

/*
   Gives a more standard RecentChanges (besides the ewiki built-in
   "UpdatedPages") in two different variants.
*/


$ewiki_plugins["rc"][0] = "ewiki_page_rc_usemod";
//$ewiki_plugins["rc"][0] = "ewiki_page_rc_moin";

define("EWIKI_PAGE_RECENTCHANGES", "RecentChanges");

$ewiki_t["en"]["DAY"] = "%a, %d %b %G";
$ewiki_t["en"]["CLOCK"] = "%H:%M";
$ewiki_t["de"]["show last"] = "zeige letzte";
$ewiki_t["de"]["days"] = "Tage";



$ewiki_plugins["page"][EWIKI_PAGE_RECENTCHANGES] = "ewiki_page_recentchanges";
function ewiki_page_recentchanges($recentchanges, $data, $action) {

   global $ewiki_plugins, $ewiki_links;

   #-- start output
   $ewiki_links = true;
   $o = "";
   $o .= ewiki_make_title($recentchanges, $recentchanges, 2);
   
   #-- options
   $minor_edits = $_GET["minor"]?1:0;

   #-- select timeframe
   if (($days = $_REQUEST["days"]) < 1) {
      $days = 7;
   }
   $timeframe = time() - $days * 24 * 3600;

   #-- fetch pages modified in given timeframe
   $result = ewiki_db::GETALL(array("meta", "lastmodified", "author"));
   $changes = array();
   $meta = array();
   while ($row = $result->get(0, 0x0137, EWIKI_DB_F_TEXT)) {

      if ($row["lastmodified"] >= $timeframe) {

         #-- id->time array
         $id = $row["id"];
         $changes[$id] = $row["lastmodified"];

         #-- collect also info for previous changes of current page
         $meta[$id] = array();
         ewiki_page_rc_more($row, $meta[$id], $timeframe, $minor_edits);
      }
   }

   #-- sort results into date catalogue
   arsort($changes);
   $last_date = "";
   $datestr = ewiki_t("DAY");
   $e = array();
   foreach ($changes as $id=>$date) {

      $date = strftime($datestr, $date);
      if ($date != $last_date) {
         $last_date = $date;
      }

      $e[$date][] = $id;
      unset($changes[$id]);
   }


   #-- mk output
   $o .= $ewiki_plugins["rc"][0]($e, $meta);


   #-- add an <form>
   if ($days == 7) {
      $days = 30;
   }
   $url = ewiki_script("", $recentchanges);
   $o .= ewiki_t(<<<EOT
   <br />
   <form action="$url" method="GET">
     <input type="hidden" name="id" value="$recentchanges">
     _{show last} <input type="text" name="days" value="$days" size="5">
     <input type="submit" value="_{days}">
   </form>
   <br />
EOT
   );

   return($o);
}


/*
   UseMod like list output
*/
function ewiki_page_rc_usemod(&$e, &$meta) {

   $clockstr = ewiki_t("CLOCK");

   foreach ($e as $datestr => $pages) {

      $o .= "\n<h4 class=\"date\">$datestr</h4>\n";

      foreach ($pages as $id) {

         $diff = '<a href="'.ewiki_script("diff",$id).'">(diff)</a>';
         $page = '<a href="'.ewiki_script("",$id).'">'.htmlentities($id).'</a>';
         $time = strftime($clockstr, $meta[$id][0][2]);
         $author = ewiki_author_html($meta[$id][0][0], 0);
         $log = htmlentities($meta[$id][0][1]);
         $changes = "";
         if (($n = count($meta[$id])) > 1) {
            $changes = "($n ".'<a href="'.ewiki_script("info", $id).'">changes</a>)';
         }

         $o .= '&middot; '
             . $diff . ' '
             . $page
             . ' ' . $time . ' '
             . $changes . ' '
             . ($log ? '<b class="log">[' . $log . ']</b>' : '')
             . ' . . . . . ' . $author
             . '<br />' . "\n";
      }
   }
   $o .= "\n";
   return($o);
}


/*
   MoinMoin table style changelog output
*/
function ewiki_page_rc_moin(&$e, &$meta) {

   $clockstr = ewiki_t("CLOCK");

   $o .= '<table class="changes" border="0" width="100%">'
      . '<colgroup><col width="35%"><col width="5%"><col width="25%"><col width="35%"></colgroup>';

   foreach ($e as $datestr => $pages) {
      $o .= "\n<tr><td colspan=\"3\"><br /><h4 class=\"date\">$datestr</h4></td></tr>\n";
      foreach ($pages as $id) {

         $link = '<a href="' . ewiki_script("", $id) . '">' . htmlentities($id) . '</a>';
         $time = strftime($clockstr, $meta[$id][0][2]);
         $changes = $meta[$id];
         if (count($changes) >= 2) {

            #-- enum unique author names
            $a = array();
            foreach ($changes as $i=>$str) {
               $str = strtok($str[0], " <(");
               $a[$str][] = ($i+1);
            }
            $str = "";
            foreach ($a as $author=>$i) {
               $author = ewiki_author_html($author, 0);
               $str .= $author. "[".implode(",",$i)."]<br /> ";
            }
            $author = $str;

            #-- enum log entries
            $log = "";
            foreach ($meta[$id] as $i=>$str) {
               if ($str = $str[1]) {
                  $log .= "#".($i+1)." " . htmlentities($str) . "<br />\n";
               }
            }
         }
         else {
            $author = ewiki_author_html($meta[$id][0][0]);
            $log = htmlentities($meta[$id][0][1]);
         }

         $o .= '<tr><td class="page"> &middot; ' . $link . '</td>'
             . '<td class="time"> [' . $time . '] </td>'
             . '<td class="author">' . $author . '</td>'
             . '<td class="log">' . $log . '</td></tr>' . "\n";
      }
   }
   $o .= "</table>\n";
   return($o);
}


/*
   fills $list with changeLOG entries of previous page ($row) versions
*/
function ewiki_page_rc_more(&$row, &$list, $timeframe, $minor_edits) {

   $id = $row["id"];
   $ver = $row["version"];
   while ($ver >= 1) {

      if ($row["lastmodified"] >= $timeframe) {
         if ($minor_edits || !($row["flags"] & EWIKI_DB_F_MINOR)) {
            $list[] = array(
               0 => $row["author"],
               1 => $row["meta"]["log"],
               2 => $row["lastmodified"],
            );
         }
      }
      else {
         return;
      }

      $ver--;
      if (!$ver || !($row = ewiki_db::GET($id, $ver))) { 
         return;  // stops at revision holes
      }
   }
}

?>