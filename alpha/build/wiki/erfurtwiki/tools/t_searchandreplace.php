<?php
  #-- remake of an existing admin/ plugin
  include("t_config.php");
  if (!function_exists("ewiki_page_searchandreplace")) {
     include("plugins/admin/page_searchandreplace.php");
  }
  #-- override some settings
  if (!defined("CONCURRENT_INCLUDE")) {
    $ewiki_config["script"] = "t_searchandreplace.php?id=";
    $ewiki_config["print_title"] = 0;
    $ewiki_ring = 0;
  }
?>
<html>
<head>
 <title>global search and replace</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>SearchAndReplace</h1>
<?php
   $id = "SearchAndReplace";
   $action = "view";
   $data = array();
   echo ewiki_page_searchandreplace($id, $data, $action);
?>
<br><br>
</body>
</html>
