<?php
  include("t_config.php");
?>
<html>
<head>
 <title>WikiCommander:PageDetails:<?php echo $id; ?></title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#0000c0" text="#eeeeee" class="Edit PageDetails Panel">
<?php

  #-- get current page data
  $id = $_REQUEST["id"];
  echo "<a href=\"edit.php?id=".urlencode($id)."\">$id</a><br>\n";
?>
<table border="0" cellpadding="2" cellspacing="0">
<colgroup cols="4"><col width="10%"><col width="20%"><col width="10%"><col width="15%"><col width="45%"></colgroup>
<tr>
 <th>Ver</th>
 <th>MTime</th>
 <th>Flags</th>
 <th>Size</th>
 <th>Author</th>
</tr>
<?php


  #-- show all versions
  $data = ewiki_db::GET($id);
  $version = $data["version"];
  for ($ver=$version; $ver>=1; $ver--) {

     $row = ewiki_db::GET($id, $ver);
     if (!$row) {
        continue;
     }

     $href = 'edit.php?id='.urlencode($id)."&version=$ver";
     echo '<tr>'
        . "<td><a href=\"$href\">#$ver</a></td>"
        . "<td><a href=\"$href\">" . strftime("%Y-%m-%d", $row["lastmodified"]) . "</a></td>"
        . "<td align=\"center\">".flag_text($row["flags"])."</td>"
        . "<td align=\"right\">".strlen($row["content"])."</td>"
        . "<td>".$row["author"]."</td>"
        . "</tr>\n";

  }

?>
</tr></table>
</body></html>