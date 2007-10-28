<?php

#-- libs
if (!function_exists("gzeof")) {
   die("This tool requires the 'zlib' extension of PHP 3.x or higher. Switch your provider.");
}
include("t_config.php");

#-- config
define("EWIKI_TRANSFER_IDF", "EWBF00000025");    // file magic

#-- downloading
if ($type = $_REQUEST["fetch"])  {
   $gzip = ($type == "dat.gz");

   $date = strftime("%Y-%m-%d", time());
   $title = EWIKI_NAME;
   header("Content-Type: application/octet-stream");
   header("Content-Disposition: attachment; filename=\"ewiki_transfer.$title.$date.$type\"");
   if ($gzip) {
      ob_start("ob_gzhandler");
   }

   echo(EWIKI_TRANSFER_IDF);
   $n=0;

   $result = ewiki_db::GETALL(array("id","version","flags"));
   while ($row = $result->get()) {
      $n++;

      $id = $row["id"];
      for ($v=$row["version"]; $v>0; $v--) {

         $row = ewiki_db::GET($id, $v);

         if ($_REQUEST["textonly"]
             && (EWIKI_DB_F_TEXT != ($row["flags"] & EWIKI_DB_F_TYPE)) )
         {
            continue;
         }

         if ($row && ($row = serialize($row))) {
             echo "\n" . strlen($row) . "\n" . $row;
         }

      }
      
      if ($gzip && !($n % 15)) {
         ob_flush();
      }
   }
}

#-- uploading
elseif (!empty($_FILES["data"])) {

#error_reporting(E_ALL);
   if ($i = gzopen($_FILES["data"]["tmp_name"], "rb")) {
      $feof = "gzeof";
      $fgets = "gzgets";
      $fread = "gzread";
   }
   elseif ($i = fopen($_FILES["data"]["tmp_name"], "rb")) {
      $feof = "feof";
      $fgets = "fgets";
      $fread = "fread";
   }
   else {
      die("could not open incoming file");
   }

   $n = 0;

   while ($i && !$eof($i)) {

      /*stripCRLF*/ $idf = $fgets($i, 4096);
      if ($n==0) {
         $idf = trim($idf);
         if ($idf != EWIKI_TRANSFER_IDF) {
            die("This is not an ewiki transfer binary. (wrong magic code 0x".bin2hex($idf).")");
         }
      }

      $count = $fgets($i, 4096);
      if (($count === false) || (($count = trim($count)) <= 0)) {

         if ($feof($i)) {
            $fclose($i);
            die("<br><b>finished reading</b> $n entries");
         }
         else {
            die("<br><b>file broken</b> (zero count block) after $n entries");
         }
      }

      $row = $fread($i, $count);
      $row = unserialize($row);

      if (ewiki_db::WRITE($row)) {
         echo $row["id"] .".". $row["version"] . " &nbsp;\n";
      }

      $n++;
   }

}

#-- activation form
else {
   ?>
<html>
<head>
 <title>make binary backup of whole database</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">

     <h1>database dump</h1>
     If you cannot make use of the <b>ewikictl</b> cmdline utility, and need
     a way to transfer the whole database from one server to another, you
     can make a downloadable binary dump using this util.
     <br><br>

     <h4>generate dump</h4>
     <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="GET" enctype="application/x-www-form-urlencoded">
       <select name="textonly">
        <option value="0">full dump (+ binary entries)</option>
        <option value="1" selected>retrieve only text pages</option>
       </select>
       <select name="fetch">
        <option value="dat.gz">.dat.gz</option>
        <option value="dat">.dat</option>
       </select>
       <br> <input type="submit" value="save">
     </form>
     <br>

     <h4>reinsert dump</h4>
     <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST" enctype="multipart/form-data">
       <input type="file" name="data">
       <br> <input type="submit" value="upload">
     </form>
     <br>

</body></html>
   <?php
}

?>