<?php
  include("t_config.php");
?>
<html>
<head>
 <title>edit ewiki page flags</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>edit page flags</h1>
<?php


  $FD = array(
     EWIKI_DB_F_TEXT => "TXT",
     EWIKI_DB_F_BINARY => "BIN",
     EWIKI_DB_F_DISABLED => "OFF",
     EWIKI_DB_F_HTML => "HTM",
     EWIKI_DB_F_READONLY => "RO",
     EWIKI_DB_F_WRITEABLE => "WR",
  );

  if (empty($_REQUEST["set"])) {

     $result = ewiki_db::GETALL(array("version", "flags"));

     echo '<form action="t_flags.php" method="POST" enctype="multipart/form-data">';
     echo '<table class="list" border="0" cellspacing="3" cellpadding="2" width="96%">' . "\n";

     while ($row = $result->get()) {
        $id = $row["id"];

        $data = ewiki_db::GET($row);

        echo '<tr><td width="40%">';
        if ($data["flags"] & EWIKI_DB_F_TEXT) {
           echo '<a href="' . ewiki_script("", $id) . '">';
        }
        else {
           echo '<a href="' . ewiki_script_binary("", $id) . '">';
        }
        echo htmlentities($id) . '</a><small>' .  (".".$row["version"]) . '</small></td>';

        echo '<td><small>';
        foreach ($FD as $n=>$str) {
           echo '<input type="checkbox" name="set['. rawurlencode($id)
                . '][' . $n . ']" value="1" '
                . (($data["flags"] & $n) ? "CHECKED" : "")
                . '>'.$str. ' ';
        }
        echo "</small></td>";

        echo "</tr>\n";

     }

     echo '</table><input type="submit" value="&nbsp;    change settings    &nbsp;"></form>';

  }
  else {

     foreach($_REQUEST["set"] as $page=>$fa) {

        $page = rawurldecode($page);

        $flags = 0;
        $fstr = "";
        foreach($fa as $num=>$isset) {
           if ($isset) {
              $flags += $num;
              $fstr .= ($fstr?",":""). $FD[$num];
           }
        }

        echo "· ".htmlentities($page)." ({$flags}=<small>[{$fstr}]</small>)";

        $data = ewiki_db::GET($page);

        if ($data["flags"] != $flags) {
           $data["flags"] = $flags;
           $data["author"] = "ewiki-tools, " . ewiki_author();
           $data["version"]++;
           ewiki_db::WRITE($data);
           echo " <b>[set]</b>";
        }
        else {
           echo " [not changed]";
        }

        echo "<br>\n";

     }

  }


  function strong_htmlentities($str) {
     return preg_replace('/([^-., \w\d])/e', '"&#".ord("\\1").";"', $str);
  }

?>