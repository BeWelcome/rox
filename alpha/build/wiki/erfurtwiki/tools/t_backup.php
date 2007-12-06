<?php

  include("t_config.php");
  ob_start(); include($lib = dirname(__FILE__)."/ewikictl"); ob_end_clean();

?>
<html>
<head>
 <title>generate backup files from ewiki pages</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>database/page backup</h1>

<?php

  if (empty($_REQUEST["backup"])) {

     ?>

    This tool is not intended to make a complete and safe backup of your ewiki
    database, use PHPMySQLAdmin for such purposes; or save raw serialized(SQL
    query results) with a dedicated script.
    <br><br>

    <form action="t_backup.php" method="get">

     <b>directory</b> to save backup files:<br>
     <input type="text" name="dest" value="/var/backup/ewiki/">
     <br><small>(either absolute or relative to location of ewiki.php)</small><br>
     <br>

     backup <b>format</b>:<br>
     <select name="format">
       <option value="plain">plain text files</option>
       <option value="flat" selected="selected">flat files (message/http)</option>
       <option value="fast">fast files (serialized)</option>
       <option value="meta">plain with companion .meta files</option>
       <option value="xml">save whole page data as pseudo xml</option>
       <option value="xmlmeta">companion .meta files in xml style</option>
     </select>
     <br><small>Note that only the first three variants can be reinserted later.<small><br>
     <br>

     <b>versions</b> store behaviour:<br>
     <select name="behaviour">
       <option value="all" selected="selected">save all versions of each page (.NNN extensions)</option>
       <option value="last">backup only the very last (newest) page version</option>
     </select><br>
     <br>

     other settings:<br>
     <input type="checkbox" name="urlencode" value="1"> <b>urlencode</b>, write dbff-filenames assuming DOS filesystem restrictions (not required on UNIX systems) <br>
     <input type="checkbox" name="force" value="1"> <b>enforce</b>, override problems (use this only if first try failed)<br>
     <br>

     <input type="submit" name="backup" value="backup">
     <br><br>

    </form>


     <?php

  }
  else {

     echo "<b>ewikictl</b>:<br>\n<br>\n\n<tt>\n";

     $config["dest"] = $_REQUEST["dest"];
     $config["format"] = $_REQUEST["format"];
     $config["force"] = $_REQUEST["force"];
     $config["urlencode"] = $_REQUEST["urlencode"];

     if ($_REQUEST["behaviour"]=="all") {
        $config["all"] = 1;
     }

     set_options_global();

     command_backup();



     echo "</tt>\n";

  }


?>
</body>
</html>