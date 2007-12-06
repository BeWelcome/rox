<?php
/*
   This MpiPlugin should be called from the BannedLinks or BlockedLinks
   pages to ease injecting fresh URLs (it strips "http://" and "www."
   prefixes, titles and thereby yields shorter matching patterns).
*/

$ewiki_plugins["mpi"]["registerspam"] = "ewiki_mpi_registerspam";

function ewiki_mpi_registerspam($action, &$args, &$iii, &$s) {

   global $ewiki_id;

   if (!$_POST["regspam"]) {
      return<<<END
<form action="$_SERVER[REQUEST_URI]" method="POST" enctype="multipart/form-data">
<textarea name="add_spam" cols="50" rows="3"></textarea><br/>
<input type="submit" name="regspam" value="add listed urls" />
</form>
END;
   }
   else {
   
      #-- scan for links
      $text = $_REQUEST["add_spam"];
      ewiki_scan_wikiwords($text, $uu);
      $ls = array();
      foreach ($uu as $href=>$uu) {
         if ($l = strpos($href, "://")) {
            // filter out redundant pattern parts
            $href = substr($href, $l + 3);
            if (strpos($href, "www.")===0) {
               $href = substr($href, 4);
            }
            $href = trim($href, "/");
            $ls[] = strtok($href, " ");
         }
      }
      
      #-- reduce
      $ls = array_unique($ls);
      $data = ewiki_db::GET($ewiki_id);
      foreach (explode("\n", trim($data["refs"])) as $href) {
         if (in_array($href, $ls)) {
            unset($ls[array_search($href, $ls)]);
         }
      }
      
      #-- add to current page
      if ($ls) {
         $inj = "* [" . implode("], [", $ls) . "]\n";
         $data["content"] = preg_replace("/(^[-*#])/m", "$inj$1", $data["content"], 1);
         ewiki_db::UPDATE($data);
         $data["version"]++;
         ewiki_db::WRITE($data);
         return "\n<div class=\"system-message ok\">new links added as patterns here (please go back and reload page to see it)</div>\n";
      }
      else {
         return "\n<div class=\"system-message failure\">no new links found, please add the patterns by hand then</div>\n";
      }
   }
}

?>