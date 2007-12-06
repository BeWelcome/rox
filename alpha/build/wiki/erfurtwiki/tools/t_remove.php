<?php
  include("t_config.php");
?>
<html>
<head>
 <title>delete ewiki pages</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>delete pages from DB</h1>
<?php


  if (empty($_REQUEST["remove"])) {

     echo "
	Note that only <b>unreferenced pages</b> will be listed here. And
	because the ewiki engine itself does only limited testing if a page is
	referenced it may miss some of them here.<br>
        If you however empty a page first, it will get listed here too.
        Various other database diagnostics are made as well.<br><br>\n";


     $result = ewiki_db::GETALL(array("version"));
     
     $selected = array();

     if (@$_REQUEST["listall"]) {
        while ($row = $result->get()) {
           $selected[$row["id"]] = "listall <br>";
        }
     }
     else
     while ($page = $result->get()) {

        $id = $page["id"];
        if (!strlen($id)) { continue; }
        $page = ewiki_db::GET($id);
        $flags = $page["flags"];

        if (!strlen(trim(($page["content"])))) {
           @$selected[$id] .= "EMPTY <br>";
        }

        $res2 = ewiki_db::SEARCH("content", $id);
        if ($res2 && $res2->count()) {
           $check2 = 1;
           while ($row = $res2->get()) {
              $check = ewiki_db::GET($row["id"]);
              $check = strtolower($check["content"]);
              $check2 &= (strpos($check, strtolower($id)) !== false);
#echo "rc({$row['id']})==>($id): $check2 <br>";
           }
           $check = $check2;
        }
        if (empty($check)) {
           @$selected[$id] .= "UNREFerenced <br>";
        }

        if ($flags & EWIKI_DB_F_DISABLED) {
           @$selected[$id] .= "disabled_page <br>";
        }

        if (($flags & 3) == 3) {
           @$selected[$id] .= "errFLAGS(bin<b>+</b>txt) <br>";
        }

        if (!($flags & 3)) {
           @$selected[$id] .= "errFLAGS(notype) <br>";
        }

        if ($flags & EWIKI_DB_F_HTML) {
           @$selected[$id] .= "warning(HTML) <br>";
        }

        if (($flags & EWIKI_DB_F_READONLY) && !($flags & EWIKI_DB_F_BINARY)) {
           @$selected[$id] .= "readonly <br>";
        }

        if (($flags & EWIKI_DB_F_READONLY) && ($flags & EWIKI_DB_F_WRITEABLE)) {
           @$selected[$id] .= "errFLAGS(readonly<b>+</b>writable) <br>";
        }

        if (strlen($page["content"]) >= 65536) {
           @$selected[$id] .= "size &gt;= 64K <br>";
        }

        if (preg_match("/\nDelete(d?Page|Me|This)\n/", $page["refs"])) {
           @$selected[$id] .= "<tt>DeleteMe</tt> <br>";
        }

     }
     


     echo '<form action="t_remove.php" method="POST" enctype="multipart/form-data">';
     echo '<input type="submit" name="listall" value="listall">';
     echo '<table class="list" border="0" cellspacing="3" cellpadding="2" width="500">' . "\n";
     echo "<tr><th>page name</th><th>error / reason</th></tr>\n";

     foreach ($selected as $id => $reason) {
        
        echo '<tr><td>';

        #-- checkbox
        echo '<input type="checkbox" value="1" name="remove[' . rawurlencode($id) . ']">&nbsp;&nbsp;';

        #-- link & id
        if (strpos($id, EWIKI_IDF_INTERNAL) === false) {
           echo '<a href="' . ewiki_script("", $id) . '">';
        }
        else {
           echo '<a href="' . ewiki_script_binary("", $id) . '">';
        }
        echo htmlentities($id) . '</a></td>';

        #-- print reason
        echo '<td>' . $reason . "</td>";

        echo "</tr>\n";

     }

     echo '</table><br><input type="submit" value="&nbsp; delete selected pages &nbsp;"></form>';

  }
  else {

     echo "<ul>\n";
     foreach ($_REQUEST["remove"] as $id => $uu) {

        $id = rawurldecode($id);

        echo "<li>purging »".htmlentities($id)."«...</li>";

        $data = ewiki_db::GET($id);
        for ($version=1; $version<=$data["version"]; $version++) {

           ewiki_db::delete($id, $version);

        }
        
     }
     echo "</ul>\n";

  }

?>