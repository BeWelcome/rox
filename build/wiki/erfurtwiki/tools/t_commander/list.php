<?php
  include("t_config.php");
?>
<html>
<head>
 <title>WikiCommander:PageList</title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#0000c0" text="#eeeeee" class="PageList Panel">
<?php

  #-- any filters
  if ($filter = $_REQUEST["filter"]) {
     $regex = preg_quote($filter);
     $regex = str_replace("\\*", ".*", $regex);
     echo "<div class=\"msg\">filter regex /^$regex$/i</div>\n";
     $regex = ":^$regex$:i";
  }

?>
<table border="0" cellpadding="2" cellspacing="0">
<colgroup cols="3"><col width="70%"><col width="10%"><col width="20%"></colgroup>
<tr>
 <th>Name</th>
 <th>Flags</th>
 <th>MTime</th>
</tr>
<?php
  
  #-- list all files
  $all = ewiki_db::GETALL(array("id","version","flags","lastmodified"));
  while ($row = $all->GET()) {

     #-- prep
     $id = $row["id"];
     $url_id = urlencode($id);
     $title = htmlentities(substr($id, 0, 32));
     
     #-- filter?
     if ($regex && !preg_match($regex, $id)) {
        continue;
     }

     #-- out
     echo "<tr onClick=\"parent.select_id(event, this, '".htmlentities($id)."')\">"
        . '<td class="fn">'
          . "<a target=\"page\" href=\"edit.php?id=$url_id\""
          . " onClick=\"return parent.click_magic(event)\">$title</a></td>"
        . '<td align="right">' . flag_text($row["flags"]) . '</td>'
        . '<td>' . strftime("%Y-%m-%d", $row["lastmodified"]) . '</td>'
        . "</tr>\n";
  }
  
  
  
?>
</table>
</body>
</html>
