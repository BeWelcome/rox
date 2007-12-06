<?php
   include("t_config.php");
?>
<html>
<head>
 <title>Revert changes</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>RevertChanges</h1>
<?php


if (($do = $_REQUEST["proceed"]) || ($_REQUEST["list"])) {

   if (!$do) {
      echo "<b>Warning</b>: running in <i>dummy mode</i> (--no-act), nothing will happen, all following messages are lies:\n<br><br>\n";
   }

   #-- params
   $m_author = $_REQUEST["match_author"];
   $m_time = $_REQUEST["time_slice"] * 3600;
   $action = $_REQUEST["action"];
   ($depth = $_REQUEST["version_depth"] - 1) or ($depth = 0);

   #-- walk through
   $result = ewiki_db::GETALL(array("id", "author", "lastmodified"));
   while ($row = $result->get()) {

         $id = $row["id"];

         #-- which versions to check
         $verZ = $row["version"];
         if ($action=="lastonly") {
            $verA = $verZ;
         }
         else {
            $verA = $verZ-$depth;
            if ($verA <= 0) { 
               $verA = 1;
            }
         }


         for ($ver=$verA; $ver<=$verZ; $ver++) {

            #-- load current $ver database entry
            if ($verA != $verZ) {
               $row = ewiki_db::GET($id, $ver);
            }
 
            #-- match
            if (stristr($row["author"], $m_author) && ($row["lastmodified"] + $m_time > time())) {
               echo "rm($id";
               #-- delete multiple versions
               if ($action=="allsince") {
                  while ($ver<=$verZ) {
                     echo " .$ver";
                     if ($do) {
                        ewiki_db::DELETE($id, $ver);
                     }
                     $ver++;
                  }
               }
               #-- or just the affected one
               else {
                  echo " .$ver";
                  if ($do) {
                     ewiki_db::DELETE($id, $ver);
                  }
               }
               echo ")<br>\n";
               break;
            }

         }#-- for($ver)

   }#-- while($row)


}
else {

   ?>
     If someone garbaged lots of pages in your Wiki, you may want to
     automatically revert those changes, by making this script delete any
     page versions that carry a certain string in the {author} field
     (usually the IP address or host name).<br><br>
     <form action="<?php echo $PHP_SELF; ?>" method="GET">
       {author} field pattern <input name="match_author" size="30" value="127.127.127.127:">
       <br>
       <small>This must be a fixed string (you cannot use * or regex), at
       best use the attackers` IP address or host name, but don't include
       the port number (because it increased with every http access).
       </small>
       <br><br>
       changes within the last <input name="time_slice" size="4" value="72"> hours
       <br><br>
       how to operate:<br>
       <select name="action" size="3">
         <option selected value="lastonly">delete only if it was the last change</option>
         <option value="allsince">version diving, also delete changes made after</option>
         <option value="the">version diving, but only purge the affected one</option>
       </select>
       <br>
       delete the last <input name="version_depth" size="2" value="5"> versions at max
       <br><br>
       <input type="submit" name="list" value="--no-act">
       <input type="submit" name="proceed" value="revert changes">
     </form>
     <?php 

}



?>
</body>
</html>
