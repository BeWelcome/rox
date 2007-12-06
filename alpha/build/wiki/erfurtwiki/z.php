<?php
/*
   RemoteProcedureCalls for ewiki
   ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
   - allows for XmlRpcToWiki (v2)
   - provides OpenSearch API (v0)
   - access wrapper for WikiSync (v0.1)
   - allows WebDAV directory browsing
   - can be used for ?binary= transfer
   
   All write-access features in the individual modules are already
   restricted by calling "fragments/funcs/auth.php" separately. This
   whole interface is however disabled for _PROTECTED_MODE setups.
*/

#-- load libs and server interface code
define("EWIKI_SCRIPT_BINARY", "z.php?binary=");
include_once("config.php");
include_once("plugins/lib/upgrade.php");

#-- surpress errors (slightly)
include_once("plugins/debug/xerror.php");
error_reporting(E_ALL^E_NOTICE);


#-- REQUIRE authentication for now
#   (TOO dangerous currently else - no full security review made yet)
require("fragments/funcs/auth.php");


#-- interfaces and protocols
if (true) {
  include_once("plugins/lib/wikiapi.php");
}
if (!function_exists("ewiki_opensearch_api")) {
  include_once("plugins/lib/opensearch.php");
}
if (!function_exists("xmlrpc")) {
  include_once("plugins/lib/xmlrpc.php");
}
if (!function_exists("phprpc")) {
  include_once("plugins/lib/phprpc.php");
}
if (!function_exists("atom_server")) {
  // include_once("plugins/lib/atom-server.php");
}
if (!class_exists("wikidav")) {
  include_once("plugins/lib/xml.php");
  include_once("plugins/lib/minidav.php");
  include_once("plugins/lib/webdav.php");
}

#-- prepare *-RPC
$xmlrpc_methods = &$wikiapi;
$phprpc_methods = &$wikiapi;

#-- fail for:
if (EWIKI_PROTECTED_MODE) {
   die("The API is disabled, because it doesn't yet respect the _PROTECTED_MODE restrictions.");
}


#-- WebDAV
if (defined("EWIKI_SCRIPT_WEBDAV") && $_SERVER["PATH_INFO"]) {
#error_reporting(E_ALL);
   $wds = new WikiDav();
   $wds->ServeRequest();
   exit;
}


#-- what kind of request
$rt = strtolower(trim(strtok(@$_SERVER["CONTENT_TYPE"], ",;")));
switch ($rt) {

   #-- WikiApi
   case XMLRPC_MIME_OLD:
   case XMLRPC_MIME_NEW:
      xmlrpc_server();
      exit;

   #-- Atom
   case "application/atom+xml":
      // atom_server();
      header("501 Not Implemented");
      exit;

   #-- WikiSync or PHP-RPC
   case "application/vnd.php.serialized":
      phprpc_server();
      include("tools/t_sync.php");
      exit;
      
   default:
}

#-- other detection mechanism?
//...


#-- else complain
header("Content-Type: text/html");
header("Status: 401 Method Not Implemented");
die('<html><head><link rel="stylesheet" type="text/css" href="tools/t_config.css"></head><body>This is the interface for XML-RPC, OpenSearch, WikiSync, PHP-RPC, <a href="z.php/">WebDAV</a> and Atom requests. Go away already!</body></html>');

?>