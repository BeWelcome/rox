<?php
/*
   An info/admin page, which also allows you to start all the scripts
   contained herein manually (if you have no real cron service available).
*/
  
  #-- load lib
  chdir("..");
  include("t_config.php");
  chdir(dirname(__FILE__));

  #-- unlock stale lock file, if requested
  $lock = EWIKI_TMP . "/ewiki-crond-runparts.$_SERVER[SERVER_NAME].lock";
  if ($_REQUEST["unlock"]) {
     unlink($lock);
  }

?>
<html>
<head>
 <title>cron.d/ centre</title>
 <link rel="stylesheet" type="text/css" href="../t_config.css">
 <script type="text/javascript"><!--
   function show_title(tag) {
      document.getElementById("titlebox").setAttribute("class", "message");
      document.getElementById("titlebox").firstChild.data = tag.getAttribute("title");
   }
   function hide_box() {
      box = document.getElementById("titlebox");
      box.removeAttribute("class");
      box.firstChild.data = " ";
   }
 //--></script>
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>manual cron.d/ run-parts activation</h1>

<table border="0" cellspacing="10">
<tr>
<td width="30%" valign="top">
<?php

  #-- read in all files
  $dh = opendir(".");
  while ($fn = readdir($dh)) {
     if (preg_match("/^[SKZ]/", $fn)) {
        $list[$fn[0]][] = $fn;
     }
  }
  closedir($dh);

  #-- and print preview/info list
  @asort($list["S"]);
  @arsort($list["Z"]);
  @arsort($list["K"]);
  $list = array_merge($list["S"], (array)$list["Z"], (array)$list["K"]);
  foreach($list as $fn) {
     $desc = "???";
     $uu = implode("", file($fn));
     if (preg_match("#/\*(.+)\*/#s", $uu, $uu)) {
        $desc = htmlentities($uu[1]);
     }
     $size = filesize($fn);
     echo "<tt title=\"$desc\" onMouseOver=\"show_title(this)\" onMouseOut=\"hide_box()\">$fn</tt> [$size]<br>";
  }

?>
</td>
<td width="70%" valign="top">

If you seriously cannot afford a provider with cron support, then you
can as last resort start the scripts in the cron.d/ directory by hand.
<br>
<br>

<form action="run-parts.php" method="POST" enctype="multipart/form-data" style="display:inline">
<input type="submit" value="run-parts">
</form>

<form action="index.php" method="POST" enctype="multipart/form-data" style="display:inline">
<input type="submit" name="unlock" value="remove stale lock" style="font-size:10px;" <?php if (!file_exists($lock)) echo "disabled";?>>
</form>

<br>
<br>
<small>
It is safe to leave this open to get started by anyone, because it is already
secured against concurrent execution and interruption and after all does
nothing what is not configured/wanted anyhow. See also the <a href="HOWTO">
HOWTO </a> file for more notes on this.
</small>

<br>
<br>

<div id="titlebox" class="hidden" style="font-size:72%">&nbsp;</div>

<br>

</td>
</tr>
</table>

<br>
<br>
<br>
<br>
</body>
</html>
