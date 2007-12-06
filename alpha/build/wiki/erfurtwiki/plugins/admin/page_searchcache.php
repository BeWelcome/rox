<?php

/*
  This plugin allows to generate text database entries for static/internal
  pages, so those can later be found by the PageSearch or PowerSearch
  functions. It may be useful to run it on a regular time period. You
  certainly need to set 'max_execution_time' limit in the php.ini up.

  The function is crippled to run in $ewiki_ring==0 only (if an admin is
  logged in). Either edit yoursite.php to detect the admin user or change
  that line of code herein to make this admin function available to the
  public.
*/
 

$ewiki_plugins["page"]["SearchCache"] = "ewiki_cache_generated_pages";


function ewiki_cache_generated_pages($id, &$data, $action) {

   global $ewiki_plugins, $ewiki_ring;

   $o = ewiki_make_title($id, $id, 1);

   if (empty($_REQUEST["generate_cache"])) {

      $o .= "Use this page plugin/tool to generate text database entries for
all generated ('internal' or 'static') pages available, so those can later
be found using the search functions.<br /><br />";

      $o .= '<form action="'.ewiki_script("",$id).'" method="POST" enctype="text/html">'
          . '<input type="hidden" name="id" value="'.$id.'">'
          . '<input type="submit" value="generate cache" name="generate_cache">'
          . '</form>';

   }
   elseif (!ewiki_auth($id, $data, $action, $ring=0, "_FORCE_AUTH=1") || !isset($ewiki_ring) || ($ewiki_ring > 0)) {

      if (is_array($data)) {
         $data = "You'll need to be admin. See ewiki_auth() and _PROTECTED_MODE in the README.";
      }
      $o .= $data;

   }
   else {
      unset($_REQUEST["generate_cache"]);

      $o .= "generating cache versions from:<ul>\n";

      foreach ($ewiki_plugins["page"] as $pid=>$pf) {

#echo "$pid:";

         $d = ewiki_db::GET($pid);
         if (empty($d) || empty($d["content"])) {
            $d = array(
               "id" => $pid,
               "version" => 1,
               "flags" => EWIKI_DB_F_TEXT,
               "created" => time(),
               "content" => "",
               "meta" => "",
               "hits" => 0,
               "refs" => "",
            );
         }

         $d["last_modified"] = time();
         $d["hits"]++;
         $d["content"] = $pf($pid, $d, "view");

         //@ADD - transform <html> back to wikimarkup
         //       here?

         if (ewiki_db::WRITE($d, true)) {
           $o .= "<li>$pid</li>\n";
         }

         unset($d);
      }

      $o .= "</ul>";
      ewiki_log("page search cache was updated", 2);
   }

   return($o);
}


?>