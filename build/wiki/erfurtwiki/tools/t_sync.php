<?php
/*
   Provides syncing (or simply transfering) pages with a remote Wiki
   installation, via special RPCs or open Wiki XML-RPC interface - in
   future.
*/

#-- load libs
define("WIKISYNC_VER", 0.1);
#$_SERVER["SERVER_NAME"] .= " {.-prevents-readonly-test-account}";
include("t_config.php");
if (!function_exists("xmlrpc")) include("plugins/lib/xmlrpc.php");
if (!function_exists("phprpc")) include("plugins/lib/phprpc.php");
if (!function_exists("ewiki_sync_local")) include("plugins/lib/sync.php");

#-- serve XML-RPC and PHP-RPC requests
header("X-WikiSync: " . WIKISYNC_VER);
$ewiki_config["ua"] .= " WikiSync/".WIKISYNC_VER;
if ($_SERVER["REQUEST_METHOD"] != "GET") {
   $phprpc_methods =
   $xmlrpc_methods =
   array(
      "ewiki_db" => "ewiki_db",
      "ewiki.sync" => "ewiki_sync_local",
   );
   phprpc_server();
   xmlrpc_server();
   die("Request Missed.");
}

#-- start
$action = $_REQUEST["action"];
$proto = $_REQUEST["proto"];
$url = $_REQUEST["url"];
if ($url) {  // save as preference
  setcookie("last_sync_url", $url, time() + 90*24*60*60, dirname($_SERVER["REQUEST_URI"]));
}

?>
<html>
<head>
 <title>syncing with remote wiki database</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>WikiSync&trade;</h1>

<?php

// error_reporting(E_ALL);
  if (!action || !$proto || !$url) {

?>
This tool allows you to maintain some pages of a public Wiki in a separate
installation but keep both synchronized. You of course must have the sync
interface installed on both systems (or at least have the XML-RPC service
enabled on the other site).

<br>
<br>

There is currently no conflict resolution code built-in, so you have to
correct these yourself, whenever you edited a page that also was edited
(by someone else) on the remote WikiWikiWeb server. So this is very safe
as it never overwrites stuff, and quick as it typically only copies the
latest version of every page.

<br>
<br>

<form action="t_sync.php" method="GET">
  <label for="proto">protocol</label>
  <select id="proto" name="proto">
    <option value="sync">WikiSync&trade; <?php echo WIKISYNC_VER; ?></option>
    <option value="db">ewiki_db::RPC</option>
    <option value="wiki">WikiXmlRpc v2</option>
  </select>
  <br>
  <label for="url">interface</label>
  <input type="text" name="url" id="url" size="48" value="<?php
     if ($url = $_COOKIE["last_sync_url"]) {
        echo $url;
     }
     else {
        echo "http://user:password@" . strtok($_SERVER["SERVER_NAME"], " ") . $_SERVER["REQUEST_URI"];
     }
  ?>">
  <br>
  <small>
     Must be either the URL to a remote <tt>z.php</tt> script or better
     to a remote <tt>t_sync.php</tt>.
  </small>
  <br>
  <br>
  <select id="action" name="action" title="action">
    <option value="sync">sync</option>
    <option value="up">upload</option>
    <option value="down">download</option>
    <option value="exact">exact</option>
  </select>
  <!-- ... -->
  <input type="submit" value="transfer">
</form>
<?php



 }

 #-- minimal checks beforehand ------------------------------------------
 elseif ($proto != "sync") {

    echo "Sorry, currently only the WikiSync&trade; protocol is supported.";

 }

 #-- get list of (remotely) existing page versions
 elseif (! ($ls_remote = ewiki_sync_remote("::LIST")) ) {

    echo "No '$proto'-connection to '$url' could be established.";

 }

 
 #-- perform sync -------------------------------------------------------
 else {

    #-- init
    set_time_limit(+2999);
    $ls_local = ewiki_sync_local("::LIST", false);
    $sync = ($action=="sync");
    $correct = 1;

    #-- info
    echo "<i>info</i>: " . count($locall) . " pages found in local Wiki database<br>\n";
    echo "&nbsp; &nbsp; &nbsp; and " . count($rlist) . " are on remote server<br>\n";
    echo "\n\n";
    flush();


    #-- upload
    if (in_array($action, array("upload", "sync", "exact"))) {
       echo "<br>\n<h3>upload</h3>\n";
       ewiki_sync_start(
          "upload",
          $ls_local, $ls_remote,
          "ewiki_sync_local", "ewiki_sync_remote"
       );
    }

    #-- download
    if (in_array($action, array("download", "sync", "exact"))) {
       echo "<br>\n<h3>download</h3>\n";
       ewiki_sync_start(
          "download",
          $ls_remote, $ls_local,
          "ewiki_sync_remote", "ewiki_sync_local"
       );
    }
    
    #-- do an in-deepth analyzation of remaining files
    if ($action == "exact") {
       echo "<br>\n<h3>sync - exact comparison</h3>\n";
       foreach ($ls_local as $id=>$ver) {
          if ($ls_remote[$id] == $ver) {

             echo htmlentities($id);
             flush();

             $L = ewiki_sync_local("::GET", array($id));
             $R = ewiki_sync_remote("::GET", array($id));
             
             if (!ewiki_sync_half_identical($L, $R)) {
                echo " - conflict";
             }
             else {
                echo " - ok";
             }
             echo "<br>\n";
          }
       }
    }



    
 
 }


?>
<br>
<br>
</body>
</html>
