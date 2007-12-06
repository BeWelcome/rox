<?php
#  include("t_config.php");
?>
<html>
<head>
 <title>WikiCommander:FileMenu</title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#222222" text="#dddddd" class="FileMenu Menu">
<div width="100%">
<?php

  $menu = array(
    "Left" => array(
       "Reload" => "parent.page.location.reload()",
       "Disable" => "parent.left_off=1, parent.page.location.href='action.php?nop=1'",
       "EditorOn" => "parent.left_off=0",
    ),
    "File" => array(
       "Edit" => "parent.page.location.href='edit.php?id='+escape(parent.id)",
       "New" => "parent.new_page()",
       "Rename" => "parent.rename_page()",
       "Copy" => "parent.copy_page()",
       "Delete" => "parent.delete_page()",
    ),
    "Command" => array(
       "CmdLineHelp" => "parent.page.location.href='action.php?cmd=1&input=help'",
       "Backup" => "",
       "Upload" => "",
       "Transfer" => "",
    ),
    "Option" => "0==0",
    "Right" => array(
       "Select" => "parent.list_selection()",
       "Filter" => "parent.show_filtered_list()",
       "Reload" => "parent.list.location.reload()",
    ),
  );
#  if ($e = $_REQUEST["which"]) {
#     $menu = $menu[$e];
#  }
  
  #-- output first-level menu
  echo "<a name=\"main\"></a>\n";
  foreach ($menu as $id=>$js) {
     if (is_array($js)) {
        $href = "#$id";
        $old = 'filemenu.php?which='.$id;
        echo "<a href=\"$href\">$id</a>\n";
     }
     else {
        echo "<a href=\"#main\" onClick=\"void($js);\">$id</a>\n";
     }
  }

  #-- output second-level menus
  foreach ($menu as $id=>$ent) {
     if (is_array($ent)) {
        echo "\n\n<br><br>\n";
        echo "<a name=\"$id\"><small><small>$id</small></small></a>\n";
        echo "<small><a href=\"#main\">back</a></small>\n";
        foreach ($ent as $id=>$js) {
           echo "<a href=\"#main\" onClick=\"void($js);\">$id</a>\n";
        }
     }
  }

?>
</div>
</body>
</html>
