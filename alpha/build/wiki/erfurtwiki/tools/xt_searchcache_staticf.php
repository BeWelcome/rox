<?php
   include("t_config.php");
?>
<html>
<head>
 <title>XT: cache static files' text into database</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>cache static files</h1>
<?php


#-- action
if ($_REQUEST["go"]) {

   $dir = $_REQUEST["dir"];
   $prefix = $_REQUEST["prefix"];
   $glob = $_REQUEST["glob"];
   
   #-- scan for files
   $ls = scan_dir($dir);
   foreach ($ls as $fn) if (fnmatch($glob, $fn)) {
      $id = "$prefix$fn";

      #-- checking for entry
      if ($data = ewiki_db::GET($id)) {
         // our cache entries don't have any flag set
         if ($data["flags"] & EWIKI_DB_F_TYPE) {
            continue;  // real page exists, definetely won't overwrite
         }
      }

      #-- read
      $content = file_get_contents("$dir/$fn");
      $data = ewiki_db::CREATE($id, 0x000);
      $data["content"] = $content;
      $data["meta"]["class"] = "search";

      #-- write
      ewiki_db::WRITE($id, "_OVERWRITE=1");
   }
   

   #-- returns only filenames (no dirs) with leading dirspec omitted   
   function scan_dir($dir, $pfix="") {
      $r = array();
      $dh = opendir($dir);
      while ($fn = readdir($dh)) {
         if (is_dir("$dir/$pfix$fn")) {
            $r += scan_dir($dir, "$pfix$fn/");
         }
         else {
            $r[] = "$pfix$fn";
         }
      }
      return($r);
   }

}

#-- help
else {

   ?>
     This tool is useful if 'yoursite.php' loads from static files and from the Wiki
     database concurrently and the URLs are therefore similar enough to warrant
     caching of static .html files into the database to use the unified search
     feature.<br>
     This is the case if you use <b>mod_rewrite</b> and have URLs like
     <tt>http://example.org/WikiPage</tt> and <tt>http://example.org/otherpage.html</tt>
     and both get displayed by your index.php script depending on if such a static page
     exists. In this case EWIKI_SCRIPT is most always set to "/".
     <br>
     <br>
     Else don't use this tool, it would generate silly database entries then
     for your setup.
     <br>
     <br>
     <form action="xt_searchcache_staticf.php" method="POST">
       directory <input type="text" name="dir" value="./static/"><br>
       (we're currently in "<?php echo getcwd(); ?>")
       <br><br>
       maps to <input type="text" name="prefix" value="static/"><br>
       (don't forget that EWIKI_SCRIPT is also prepended to this)       
       <br><br>
       file types <input type="text" name="glob" value="*.htm*"><br>
       (unix filename globbing!)
       <br><br>
       <input type="submit" value="Generate" name="go">
     </form>
     <?php 

}



?>
</body>
</html>
