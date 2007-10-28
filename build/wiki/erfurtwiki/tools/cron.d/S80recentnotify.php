<?php
/*
   Everybody who leaves a mail address on the "RecentNotify" page, will
   get a summary of all made edits, once in a week (configured to 8 days
   per default).
*/

define("RECENTNOTIFY", "RecentNotify");   // special page name (w/ subscribers)
define("RECENTNOTIFY_DAYS", 8);           // in days


#-- do
if (RECENTNOTIFY_DAYS && ($data = ewiki_db::GET(RECENTNOTIFY))) {
   echo "[$cron]: checking for " . RECENTNOTIFY . " subscribers\n";

   #-- look up subscribers
   ewiki_scan_wikiwords($data["content"], $uu, $_strip_email=0);
   $subscribers = array();
   if ($uu) foreach ($uu as $str=>$x) {
      if (strpos($str, "@")) {
         if (strpos($str, "notify:")!==false) {
            $str = substr($str, strpos($str, ":"));
         }
         $subscribers[] = $str;
      }
   }
   
   #-- only calc the RC if we have at least one interested
   if ($subscribers) {
      echo "[$cron]: ".count($subscribers)." listed (".implode(", ", $subscribers).")\n";
      $min_time = time() - RECENTNOTIFY_DAYS * 24 * 3600;
      $rc = array();
      $vers = array();
      $mail = "";

      #-- find pages changed in given timeframe
      $all = ewiki_db::GETALL(array("id", "version", "flags", "lastmodified"));
      while ($row = $all->get(0, 0x137f)) {

         if ($row["lastmodified"] >= $min_time) {
            $rc[$row["id"]] = $row["lastmodified"];
            $vers[$row["id"]] = $row["version"];
         }
      }

      #-- go through rc list
      echo "[$cron]: generating RC list\n";
      arsort($rc);
      $lastdatestr = "";
      do {

         #-- get entry
         reset($rc);
         list($id, $lm) = each($rc);
         $ver = $vers[$id];
         array_shift($rc);
         
         #-- output
         $row = ewiki_db::GET($id, $ver);
         $m_ver = $row["version"];
         ($m_log = $row["meta"]["log"]) and ($m_log = " . [{$m_log}] . .");
         $m_author = $row["author"];
         $m_ua = $row["meta"]["user-agent"];
         $m_time = strftime("%H:%M", $lm);
         $m_flags = "";
         if ($row["flags"] & EWIKI_DB_F_MINOR) {
            $m_flags .= " MINOR EDIT";
         }
         if ($row["flags"] & EWIKI_DB_F_APPENDONLY) {
            $m_flags .= " (append-only)";
         }
         if ($row["flags"] & EWIKI_DB_F_HIDDEN) {
            $m_flags .= " (hidden page)";
         }
         $datestr = strftime("%Y-%m-%d, %a", $lm);
         if ($lastdatestr != $datestr) {
            $lastdatestr = $datestr;
            $mail .= "\n$datestr\n";
         }
         $mail .= "· {$id} - [{$m_ver}]{$m_flags} {$m_time} . . .{$m_log} by {$m_author} / {$m_ua}\n";

         #-- check previous version of this page
         if (($ver--) && ($row = ewiki_db::GET($id, $ver))) {
            if ($row["lastmodified"] >= $min_time) {
               $vers[$id] = $row["version"];
               $rc[$id] = $row["lastmodified"];
               arsort($rc);
            }
         }
      }
      while ($rc);
      
      #-- send it
      echo "$mail";
      $subj = RECENTNOTIFY ." on ". EWIKI_NAME;
      $to = implode(", ", $subscribers);
      $mail = "This is the full list of latest changes on " . EWIKI_NAME . ".\n"
            . ewiki_script_url("", EWIKI_PAGE_INDEX) . "\n"
            . "Unsubscribe yourself on " . ewiki_script_url("edit", RECENTNOTIFY) . "\n"
            . "\n"
            . $mail
            . "\n\n-- \nThere is no Web like WikiWikiWeb.\n";
      mail($to, $subj, $mail);
   }
}


?>