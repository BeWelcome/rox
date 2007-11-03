<?php
  #-- remake of an existing admin/ plugin
  include("t_config.php");
  if (!function_exists("ewiki_page_textupload")) {
     include("plugins/page/textupload.php");
  }
  #-- override some settings
  if (!defined("CONCURRENT_INCLUDE")) {
    $ewiki_config["script"] = "t_textupload.php?id=";
    $ewiki_config["print_title"] = 0;
    $ewiki_ring = 0;
  }
?>
<html>
<head>
 <title>Upload text files as WikiPage</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>TextUpload</h1>
<?php
   $id = "TextUpload";
   $action = "view";
   $data = array();
   echo ewiki_page_textupload($id, $data, $action);
?>
<br><br>
</body>
</html>
