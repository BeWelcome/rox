<?php
  include("t_config.php");
?>
<html>
<head>
 <title>strip old versions of ewiki pages</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
<script language="JavaScript">
 function sel_all() {
    var ls = document.forms[0].elements;  // getElementsByTagName("input");
    for (var i=0; i<ls.count(); i++) {
       if (ls[i].type == "checkbox") {
          ls[i].checked = ! ls[i].checked;
       }
    }
 }
</script>
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>create page version holes</h1>
<?php


define("N_PAGE_VERSIONS", 1);


  if (empty($_REQUEST["range"])) {

     echo '
This tool can be used to remove old page versions from the database, if
they just slow down your wiki. For a db_flat_files/db_fast_files powered
ewiki you could just delete the files from the database directory.
<br><br>
Please note, that the right number is always treated as count from the
last existing version. So "2..-10" would delete anything from the 2nd to
the "LAST minus 10"-th version.
<br><br>
<form action="t_holes.php" method="POST">
<table class="list" border="0" cellpadding="2" cellspacing="3">
';

     $result = ewiki_db::GETALL(array());
     while ($row = $result->get()) {

        if (($n=$row["version"]) >= N_PAGE_VERSIONS) {

           $id = $row["id"];

           echo '<tr>';
           echo "<td>".htmlentities($id)." (#$n)</td>";
           $n2 = $n - 10;
           echo '<td> <input type="checkbox" name="id['.rawurlencode($id).']" value="1">'.
                ' delete versions ' .
                '<input name="range['.rawurlencode($id).']" value="2..'.$n2.'" size="7"> </td>';
           echo "</tr>\n";

        }

     }

     echo '
</table>
<br><input type="submit" value="strip page versions"><br>
</form>
<br>
[<a href="javascript:sel_all();">select all</a>]
<br>
<br>
Eventually you should consider using the <tt>ewikictl</tt> cmdline
utility in favour of this www script.
<br><br>
     ';

  }
  else {

     echo "purging page versions:<br>";

     $range = $_REQUEST["range"];

     foreach ($_REQUEST["id"] as $id_ue => $go) {
        $id = rawurldecode($id_ue);

        if ($go) {

           if (preg_match('/^(\d+)[-\s._:]+(\d+)$/', trim($range[$id_ue]), $uu)) {

              $versA = $uu[1];
              $versZ = $uu[2];
              echo "'".htmlentities($id)."' versions {$versA}..{$versZ}<br>\n";

              for ($v=$versA; $v<=$versZ; $v++) {

                 ewiki_db::DELETE($id, $v);

              }
              
           }
           else {

              echo "wrong range param for '$id'!<br>\n";

           }

        }
     }

  }



?>