<?php
 # include("t_config.php");
?>
<html>
<head>
 <title>WikiCommander:KeyMenu</title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#222222" text="#dddddd" class="KeyMenu Menu">
<div width="100%"><nobr>
<?php

  $menu = array(
    1 => array("Help", '"info.php" target="page"'),
    2 => array("Save", ""),  //"window.PageDetails.form[0].submit()"
    3 => array("Show", "parent.page.location.href='../../?id='+parent.id"),
    4 => array("Edit", "parent.page.location.href='edit.php?id='+parent.id"),
    5 => array("Copy", "parent.copy_page()"),
    6 => array("Move", "parent.rename_page()"),
    8 => array("Del&nbsp;", "parent.delete_page()"),
    9 => array("Menu", '"filemenu.php" target="menu"'),
    10 => array("Quit", '".." target="_parent"'),
  );
  foreach ($menu as $num=>$uu) {
     list($id, $href) = $uu;
     if (strpos($href, '"')===false) {
        $href = '"javascript:void('.$href.');"';
     }
     echo " $num" . "<a href=$href>$id</a>\n";
  }

?>
</nobr></div>
</body>
</html>
