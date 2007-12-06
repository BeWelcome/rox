<?php

  include("t_config.php");
  ob_start(); include($lib="ewikictl"); ob_end_clean();

?>
<html>
<head>
 <title>restore database entries from backup</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>restore database</h1>

<?php

  if (empty($_REQUEST["restore"])) {

    ?>

    This is a wrapper script around <b>ewikictl</b> to reinsert saved
    database entries back into the database. It also incorporates functions
    from the previous "backdown" utility.
    <br>
    To use it you must first upload a bunch of db files to a directory on
    your webserver you have access to.

    <br><br>

    <form action="t_restore.php" method="get">

     <b>directory</b> containing backup files:<br>
     <input type="text" name="incoming" value="/var/backup/ewiki/">
     <br><small>(either absolute or relative to location of ewiki.php)</small><br>
     <br>

     backup <b>format</b>:<br>
     <select name="format" size="3">
       <option value="plain">plain text files</option>
       <option value="flat" selected="selected">flat files (message/http)</option>
       <option value="fast">fast files (serialized)</option>
     </select><br>
     <br>

     <b>versions</b> insert behaviour:<br>
     <select name="behaviour" size="3">
       <option value="all" selected="selected">files come in multiple versions (.NNN extensions)</option>
       <option value="initial">single version, insert only if not already in db</option>
       <option value="append">single version, append as last entry</option>
     </select><br>
     <br>

     other settings:<br>

     <input type="checkbox" name="overwrite" value="1"> <b>overwrite</b>,
        replace existing versions (see multiple versions above) <br>

     <input type="checkbox" name="urlencode" value="1"> <b>urldecode</b>,
        read dbff-filenames assuming DOS filesystem restrictions<br>
        <small>(only matters for plain text files, because dbff files
        always carry an unencoded $id inside)</small> <br>

     <input type="checkbox" name="force" value="1"> <b>enforce</b>,
        override problems (use this only if first try failed)<br>

     <br>

     <input type="submit" name="restore" value="restore">
     <br><br>

    </form>

    <?php

  }

  else {

     echo "<b>ewikictl</b>:<pre width=\"60\">";

     $config["dest"] = $_REQUEST["incoming"];
     $config["format"] = $_REQUEST["format"];
     $config["force"] = $_REQUEST["force"];
     $config["urlencode"] = $_REQUEST["urlencode"];
     $config["insert"] = "1";

     if ($_REQUEST["behaviour"]=="all") {
        $config["all"] = 1;
        $config["keep"] = (!$_REQUEST["overwrite"]);
     }
     else {
        $config["all"] = 0;
        $config["keep"] = ($_REQUEST["behaviour"] != "append");
     }

     set_options_global();

     command_insert();

     echo "</pre>";

  }

?>

</body>
</html>