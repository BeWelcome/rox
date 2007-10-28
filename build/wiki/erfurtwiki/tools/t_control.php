<?php
  #-- remake of an existing admin/ plugin
  include("t_config.php");
  include("plugins/admin/control.php");
  #-- override some settings, if not concurrently included
  if (!defined("CONCURRENT_INCLUDE")) {
    $ewiki_config["script"] = "t_control.php?id=";
    $ewiki_config["print_title"] = 0;
    $ewiki_ring = 0;
  }
?>
<html>
<head>
 <title>single page control (combined admin features)</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<?php

   #-- id + title
   $id = ewiki_id();
   if ($l = strpos($id, "/")) {
      $id = substr($id, $l+1);
   }
   echo "<h1>control page '" .htmlentities($id) . "'</h1>\n\n";

   #-- exec plugin
   if ($data = ewiki_db::GET($id)) {
      $action = "control";
      echo str_replace("<hr>", "", ewiki_action_control_page($id, $data, $action));
   }
   else {
      echo "<b>error</b>: page does not exist\n<br>\n\n<br><br>\n";
   }

?>
</body>
</html>
