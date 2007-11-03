<?php

/*
   Inserts any REFERER url at the bottom of pages. Beforehand it validates
   that the given URL isn't forged (can be accessed directly, and contains
   an URL to the local resource - fuzzy matching). It uses the system
   user name "RefererLog", but can also be made invisible in RecentChanges.
   
   @feature: refererlog
   @depends: http, shutdown, patchsaving
   @author: mario
   @version: 1
   @title: RefererLog
   @desc: logs which pages link to it at the bottom of each page (validates existence of backlink)
   @config: EWIKI_REFERER_NOISE=1 -- show discovered backlinks on RecentChanges
*/

define("EWIKI_REFERER_NOISE", 2);


#-- activate I
if ($_SERVER["HTTP_REFERER"]) {
   $ewiki_plugins["view_final"][] = "ewiki_view_referer_test";
}


#-- activate II
function ewiki_view_referer_test(&$o, $id, &$data, $action) {
   global $ewiki_plugins;
#   if (!strpos($data["refs"], $_SERVER["HTTP_REFERER"]) && !strpos($_SERVER["HTTP_REFERER"], $_SERVER["SERVER_NAME"])) {
#      $ewiki_plugins["shutdown"][] = "ewiki_shutdown_referer_log";
#   }
ewiki_shutdown_referer_log($id, $data, $action);
}


#-- activate III
function ewiki_shutdown_referer_log($id, &$data, $action, $args=NULL) {
   global $ewiki_config;
   $iw = $ewiki_config["interwiki"];
 
   #-- the referer url
   $ref = strtok($_SERVER["HTTP_REFERER"], "# ");
   $this1 = EWIKI_SERVER . $_SERVER["REQUEST_URI"];
   $this2 = ewiki_script("", $id);
   
   #-- pattern of ourselfs
   $host = $_SERVER["HTTP_HOST"];
   $pat = substr($host, strpos($host, ".") + 1);
   if (!strpos($pat, ".")) {
      $pat = $host;
   }
   

   #-- reject if self-referring
   if (strpos($ref, $host) || strpos($ref, $_SERVER["SERVER_NAME"])) {
      return(false);
   }
   #-- reject search engine links
   if (strpos($ref, "?") && strpos($ref, "q=")) {
      return(false);
   }

   #-- link already on page?
   $sref = trim($ref, "/");
   $sref = substr($sref, strpos($sref, ".") + 1);
   $sref = strtolower($sref);
   if (strpos(strtolower($data["refs"]), $sref)) {
      return(false);
   }


   #-- forgery test 1
   if (strpos(urldecode($ref), $pat) || strpos(urldecode(urldecode($ref)), $pat)) {
      ewiki_log("forged REFERER '$ref' to $this1");
      return(-1);
   }
   #-- already banned?
   if (function_exists("ewiki_banned_link") && ewiki_banned_link($ref)) {
      ewiki_log("banned REFERER '$ref' to $this1");
      return(-1);
   }

   #-- special cases
   if (!strpos(trim(substr($ref, 10), "/"), "/")) {
      $likely_fake = 1;  // link from server root dir?
   }
   elseif (strpos($ref, "slashdot")) {
      $from_sd = 1;
   }


   #-- decode InterWiki URLs into "prefix:PageName" representation
   if ($link = ewiki_url2wiki($ref)) {
      if (stristr($data["refs"], $link)) {   // already in page
         return(false);
      }
   }
   else {
      $link = $ref;
   }
   
   
   #-- retrieve page to check for link existence
   $R = ewiki_http_query("GET", $ref, NULL, array(), "cookies.txt");
   if (!stristr($R[0], $this1) && !stristr($R[0], $this2) && !(strpos($R[0], EWIKI_NAME.":$id"))) {
      ewiki_log("faked REFERER '$ref' to $this1");
      if ($likely_fake && ($abuse = $_SERVER["HTTP_FROM"])) {
         mail(
           $abuse,
           "REFERER Header Abuse",
           "Dear 'search-engine' maintainer,\n\nYou misused the HTTP Referer: header for marketing purposes.\nThis informational mail is meant to annoy you likewise.\n\n",
           "X-From: $_SERVER[SERVER_ADMIN]\nX-Mailer: ewiki:refererlog\n"
         );
      }
      return(-1);
   }


   #-- all tests passed, add link
   $data = ewiki_db::GET($id);
   if ($data["version"]++) {
      $data["content"] = trim($data["content"])
                       . "\n- $link\n";
      ewiki_data_update($data);
      $data["author"] = "RefererLog; " . $data["author"];
      if (!EWIKI_REFERER_NOISE) {
         $data["flags"] |= EWIKI_DB_F_MINOR;
      }
      ewiki_db::WRITE($data);
   }

}


#-- decode URL to InterWiki link
function ewiki_url2wiki($url) {
   global $ewiki_config;
   foreach ($ewiki_config["interwiki"] as $moniker=>$s) {
      if (strlen($s) <= 17) {
         continue;
      }
      ($r = strpos($s, "?")) or ($r = strrpos($s, "/"));
      $s = substr($s, 0, $r - 1);
      if (strncmp($url, $s, strlen($s)) == 0) {
         $page = substr($url, strlen($r));
         preg_match("°(([".EWIKI_CHARS_U."]+[".EWIKI_CHARS_L."]+){2,}[\w\d]*)°", $page, $uu);
         if ($page = $uu[1]) {
            return("$moniker:$page");
         }
         return(false);
      }
   }
}


#-- simple HTTP requests
function ewiki_http_query($method, $url, $params=0, $req_headers=0, $cookies=0) {
   $R = array(
     0 => implode("", file($url))
   );
   return($R);
}


?>