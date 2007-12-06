<?php
/*
   Allows http "POST" and "PUSH" requests with a Content-Type of
   "application/vnd.php.serialized". This isn't used in the wild.
*/

if (empty($_POST)
and (strtoupper($_SERVER["REQUEST_METHOD"][0]) == "P")
and (strtolower(trim(strtok($_SERVER["CONTENT_TYPE"], ";,(")))
     == "application/vnd.php.serialized"))   
{
   #-- search for bare request body
   if (isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
      $_POST = $GLOBALS["HTTP_RAW_POST_DATA"];
   }
   else {
      $f = fopen("php://input", "rb");
      $_POST = fread($f, 1<<22);
      fclose($f);
   }

   #-- uncompress and decode, if something found
   if ($_POST) {

      #-- strip known/supported encodings
      $enc = trim(strtok(strtolower($_SERVER["HTTP_CONTENT_ENCODING"]), ",;"));
      if ($enc == "deflate") {
         $_POST = gzinflate($_POST);
      }
      elseif ($enc == "compress") {
         $_POST = gzuncompress($_POST);
      }
      elseif ($enc == "gzip") {
         $_POST = function_exists("gzdecode") ? gzdecode($_POST) : gzinflate(substr($_POST, 10, strlen($_POST) - 18));
      }
      elseif (($enc == "x-bzip2") or ($enc == "bzip2")) {
         $_POST = function_exists("bzdecompress") ? bzdecompress($_POST) : NULL;
      }

      #-- decipher
      if ($_POST) {
         $_POST = unserialize($_POST);
      }
      #-- merge
      if ($_POST) {
         $_REQUEST = array_merge($_REQUEST, $_POST);
      }

   }
}

?>