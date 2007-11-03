<?php
/*
   This is an convinience wrapper around the ewiki PowerSearch page. It
   should be used to catch 404 errors ("page not found") and let the
   wiki try to find the correct page.

   To set it up, link this wrapper (or move it) to the docroot or where your
   ewiki wrapper script is located. Then edit the .htaccess and add
   following (for Apache webservers):

          ErrorDocument  404  404finder.php
   
   This wrapper should then call your ewiki wrapper/layout script, this is
   what the $include variable is for:
*/

$include = "example-1.php";


#-- try to guess the 'search string' from the requested URL path
if (isset($_REQUEST["id"])) {
   # fine, do nothing
}
else {
   #-- get CGI env var
   ($url = $_SERVER["REDIRECT_URL"])
   or
   ($url = $_SERVER["PATH_INFO"])
   or
   ($url = $_SERVER["REQUEST_URI"]);

#echo "using $url";

   #-- extract $_REQUEST, strip query_string
   if ($p = strpos($url, "?")) {
      if (empty($_GET)) {
         parse_str(substr($url, $p), $_GET);
         $_REQUEST = array_merge($_REQUEST, $_GET);
      }
      $url = substr($url, 0, $p);
   }

   #-- strip existing parts out of the URL
   $pwd = getcwd();
   chdir($_SERVER["DOCUMENT_ROOT"]);
   $url = trim($url, "/");
   $new_url = false;
   $l = 0;
   while ($l = strpos($url, "/", $l+1)) {
#echo ",$url;$l, ";
      if (file_exists("./" . substr($url, 0, $l))) {
#echo "NURL";
         $new_url = substr($url, $l+1);
      }
   }
   if ($new_url) {
      $url = $new_url;
   }
   chdir($pwd);

#echo " as '$url' ";

   #-- now use it as search string
   $_REQUEST["id"] = "PowerSearch";
   $_REQUEST["where"] = "content";
   $_REQUEST["q"] = strtr("$url", "/", " ");
   unset($_SERVER["QUERY_STRING"]);
}

#print_r($_SERVER);
#print_r($_REQUEST);



foreach (array($include,"example-1.php","index.php") as $include) {
   for ($subdir=0; $subdir<3; $subdir++) {
      $dir = str_repeat("../",$subdir);
      if (file_exists($dir.$include)) {
         chdir($dir);
         include($include);
         break 2;
      }
   }
}

?>