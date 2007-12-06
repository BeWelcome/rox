<?php
  include("t_config.php");
?>
<html>
<head>
 <title>insert plain text files as pages into ewiki db</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>text file insertion util</h1>

<?php


  if (empty($_REQUEST["from"])) {

     ?>

 Use this tool to (re)insert text files as pages into the database (like the
 automatic initialization if ewiki.php, when it is run for the very first
 time).  Before it overwrites anything you must first select the files to be
 inserted from the list of found files, in the subdirectory you can select
 now: <br><br>

 <FORM ACTION="t_textinsert.php" METHOD="GET">
 subdir with page files: <INPUT NAME="from" VALUE="./init-pages">
 <br><br>
 <INPUT type="submit" VALUE="&nbsp;  list files  &nbsp;">
 </FORM>

     <?php

  }
  elseif (empty($_REQUEST["insert"])) {

     if (strstr(substr($from, 3), "/")) die("unallowed subdir name");

     $files = array();
     if ($dh = opendir($from = $_REQUEST["from"] . "/")) {

        while ($fn = readdir($dh)) {

           if (is_file($from . $fn)) {
              $files[]  = $fn;
           }

        }
        closedir($dh);
     }

     echo '<FORM ACTION="t_textinsert.php" METHOD="GET">' .
          '<INPUT NAME="from" TYPE="hidden" VALUE="'.rtrim($from, "/").'">';

     echo '<TABLE BORDER="0" CELLSPACING="3" CELLPADDING="2">';

     foreach ($files as $fn) {

        $sel = 0;
        $reason = "";

        $data = ewiki_db::GET($fn);

        if (empty($data)) {
           $sel = 1;
           $reason .= "<b>not yet in database</b><br>\n";
        }
        elseif (!strlen(trim($data["content"]))) {
           $sel = 1;
           $reason .= "<b>database entry empty</b><br>\n";
        }
        elseif ($data["lastmodified"] < filemtime($from.$fn)) {
           $sel = 1;
           $reason .= "<b>database entry is older</b><br>\n";
        }
        elseif (strlen($data["content"]) < filesize($from.$fn)) {
           $sel = 1;
           $reason .= "<b>database entry is shorter</b><br>\n";
        }

        if (strlen($data["content"]) > filesize($from.$fn)) {
           $sel = 0;
           $reason .= "database entry is <i>longer</i>!<br>\n";
        }

        if ($data["lastmodified"] >= filemtime($from.$fn)) {
           $sel = 0;
           $reason .= "database entry is <i>newer</i>!<br>\n";
        }

        if (!filesize($from . $fn)) {
           $sel = 0;
           $reason .= "empty file<br>\n";
        }

        echo '<TR><TD BGCOLOR="#9090B0">';
        echo '<INPUT NAME="file[' . $fn . ']" TYPE="checkbox" VALUE="1" ' . ($sel ? " CHECKED" : "") . '>';
        echo " " . $fn;
        echo "</TD>\n" . '<TD BGCOLOR="#9090B0">';
        echo $reason . "</TD></TR>\n";

     }
     
     echo '</TABLE>' .
          '<INPUT TYPE="submit" NAME="insert" VALUE="&nbsp;  insert files  &nbsp;">' .
          '</FORM>';     

  }
  else {

     $from = $_REQUEST["from"];
     $files = $_REQUEST["file"];
     if (strstr(substr($from, 3), "/")) die("unallowed subdir name");
     $from .= "/";

     foreach ($files as $fn => $uu) {

        if (strstr($fn, "/") ||strstr($fn, ".") || (!$uu)) {
           echo "filename '$fn' not allowed (NOTE: no versioned pages!)...<br>\n";
           continue;
        }

        $ctime = filectime($from . $fn);
        $content = implode("", file($from . $fn));

        $prev = ewiki_db::GET($fn);

        $data = array(
           "id" => $fn,
           "version" => 1+ @$prev["version"],
           "author" => ewiki_author("ewiki_backdown"),
           "flags" => EWIKI_DB_F_TEXT | @$prev["flags"],
           "content" => $content,
           "created" => $ctime,
           "lastmodified" => time(),
           "refs" => "\n",
           "meta" => "",
           "hits" => 0+ @$prev["hits"],
        );

        $r = (ewiki_db::WRITE($data) ? "ok" : "error");

        echo "writing '$fn'... [$r]<br>\n";
     }

  }

?>
</body>
</html>