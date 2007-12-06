<?php

/*
   PingBack [http://www.hixie.ch/specs/pingback/pingback] automatically
   discovers when somebody inserts a URL into a page and pings the remote
   server to take note about the mentioning of one of its articles. There
   is a lot of auto-discovery involved, and this requires XML+RPC and the
   HTTP extensions (see plugins/lib/) to be present.
   This plugin implements client and server (Wiki can itself be pinged).
   
   GOTCHAS: may only work for ordinarily named WikiPages (no non-word
   characters inside). You should prepare EWIKI_SCRIPT_URL to yield nice
   links to your pages as usual.
   If possible you should add <link rel="pingback" href=".../z.php"> to
   your site layout script.
*/



#-- plugin registration
$wikiapi["pingback.ping"] = "ewiki_pingback_rpc";
$ewiki_plugins["edit_save"][] = "ewiki_pingback_newurls";
$ewiki_plugins["init"][] = "ewiki_pingback_header";
@$ewiki_config["ua"] .= " PingBack/0.2";


#-- server part (adding URLs to our pages) -------------------------------
function ewiki_pingback_rpc($source_url, $target_url) {
   global $ewiki_config;

   #-- does the target URL refer to a known WikiPage ?
   $id = ewiki_url2id($target_url);
   if (!$id) {
      xmlrpc_send_response(xmlrpc_error(0x0021, "Could not determine PageName for the given target URL."));
   }
   if (!($data = ewiki_db::GET($id))) {
      xmlrpc_send_response(xmlrpc_error(0x0020, "The given target page does not exist."));
   }

   
   #-- check if the caller really has a link as he claims
   ini_set("user_agent", $ewiki_config["ua"]);
   if ((strpos($source_url, "http://")===0) && ($test = ewiki_http_asis($source_url, 96256))) {
      $test = strtolower($test);
      $test_url = strtolower($target_url);
      if (!strpos($test, $test_url)
      and !strpos($test, htmlentities($test_url))) {
         return xmlrpc_error(0x0011, "Sorry, but couldn't find a link to '$target_url' on your given '$source_url' page.");
      }
   }
   else {
      return xmlrpc_error(0x0010, "Your given source URL does not exist, could not be retrieved.");
   }
   
   #-- reject other frivolous links
   if (preg_match('#^http://[^/]+/?$#', $source_url)) {
      return xmlrpc_error(0x0011, "Rejected '$source_url' as frivolous.");
   }
   #-- check write permissions
   if ((EWIKI_DB_F_TEXT != $data["flags"] & EWIKI_DB_F_TYPE)
   or ($data["flags"] & EWIKI_DB_F_READONLY)) {
      return xmlrpc_error(0x0031, "Sorry, but this page is write-protected or not a system page.");
   }
   #-- already on page
   if (strpos($data["content"], $source_url)) {
      return xmlrpc_error(0x0030, "The given link does already exist on this page.");
   }
   #-- other go-away cases
   if (function_exists("ewiki_banned_url") && ewiki_banned_url($source_url) || function_exists("ewiki_blocked_url") && ewiki_blocked_url($source_url)) {
      return xmlrpc_error(0x0100, "Your link is unwanted here (registered on BlockedLinks or BannedLinks).");
   }
   
   #-- else update page
   $data["content"] = rtrim($data["content"])
                    . "\n* $source_url (PingBack)\n";
   ewiki_db::UPDATE($data);
   $data["version"]++;
   $ok = ewiki_db::WRITE($data);

   #-- fin response
   if ($ok) {
      return("Link to '$source_url' was added to page '$id'.");
   }
   else {
      return xmlrpc_error(0x0101, "Seems like a database/writing error occoured.");
   }
}


#-- page id from absolute URL
function ewiki_url2id($url) {
   if (strpos($url, EWIKI_SCRIPT_URL)===0) {
      $id = substr($url, strlen(EWIKI_SCRIPT_URL));
   }
   elseif ($l = strpos($url, "?id=")) {
      $id = strtok(substr($url, $l+4), "&");
   }
   elseif ($l = strpos($url, "?")) {
      $id = strtok(substr($url, $l+1), "&");
   }
   elseif (($l = strrpos($url, "/")) > 10) {
      $id = strtok(substr($url, $l+1), "?&.");
   }
   return($id);
}


#-- notify clients that we're also server
function ewiki_pingback_header() {
   header("X-PingBack: " . EWIKI_BASE_URL . "z.php");
}




#-- client part (notify remote server of added URLs on current site) -----
function ewiki_pingback_ping($source, $target) {

   #-- detect if $target URL is pingback-enabled, and go
   if ($rpc_url = ewiki_pingback_discover($target)) {

      $res = xmlrpc_request($rpc_url, "pingback.ping", array($source, $target));
      // we don't care about the result, do we?
   }
}


#-- short http request to discover X-Pingback header or <link> tag
function ewiki_pingback_discover($url) {
   global $ewiki_config;

   ini_set("user_agent", $ewiki_config["ua"]);
   if ((strpos($url, "http://") === 0) && ($data = ewiki_http_asis($url, 4096))) {
      if (preg_match('/\nX-Pingback:\s*([^\s,]+)/i', $data, $uu)) {
         return($uu[1]);
      }
      elseif (preg_match('/<link[^>]+rel=["\']?pingback["\']?[^>]+href=["\']?([^>"\'\s]+)/i', $data, $uu)) {
         return($uu[1]);
      }
   }
}


#-- undeciphered GET request
function ewiki_http_asis($url, $maxsize=8192) {
   global $ewiki_config;
   $c = parse_url($url);
   extract($c);
   $port = $port ? $port : 80;
   $path .= $query ? "?$query" : "";
   if ($f = fsockopen($host, $port, $errno, $errstr, $timeout=5)) {
      fwrite($f, "GET $path HTTP/1.0\r\n"
               . "Host: $host\r\n"
               . "Connection: close\r\n"
               . "Accept: text/html, application/xml, text/xml, application/xhtml+xml, text/plain\r\n"
               . "User-Agent: $ewiki_config[ua]\r\n"
               . "\r\n");
      socket_set_blocking($f, true);
      $data = false;
      while (!feof($f) && (strlen($data) < $maxsize)) {
         $data .= fread($f, $maxsize);
      }
      fclose($f);
      return($data);
   }
}



#-- check for newly added urls
function ewiki_pingback_newurls(&$save, &$old) {

   global $ewiki_plugins, $ewiki_config;

   #-- check newly added links
   $newlinks = array_diff(explode("\n", trim($save["refs"])), explode("\n", trim($old["refs"])));
   foreach ($newlinks as $link) if (strpos($link, "://")) {
      $ewiki_config["pingback"][] = $link;
   }
   if (@$ewiki_config["pingback"]) {
      register_shutdown_function("ewiki_pingback_start");
   }
}


#-- registers all previously added URLs
function ewiki_pingback_start() {

   global $ewiki_plugins, $ewiki_config, $ewiki_id;
   $source_url = ewiki_script_url("", $ewiki_id);

   foreach ($ewiki_config["pingback"] as $target_url) {
      ewiki_pingback_ping($source_url, $target_url);
   }
}


?>