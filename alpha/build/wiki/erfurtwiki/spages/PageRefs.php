<?php
/*
   page list, ordered by count of backlinks
*/

 echo ewiki_make_title($id, $id, 2);

 #-- fetch all pages
 $exist = array() + ewiki_array($ewiki_plugins["page"]);
 $refs = array();
 $result = ewiki_db::GETALL(array("refs"));
 while ($r = $result->get(0, 0x0137, EWIKI_DB_F_TEXT)) {
    $id = $r["id"];
    $exist[strtolower($id)] = 1;
    foreach (explode("\n", trim($r["refs"])) as $i) {
       $refs[$i]++;
    }
 }
 unset($refs[0]);

 #-- beatify list
 if (isset($_REQUEST["desc"])) {
    asort($refs);
 }
 else {
    arsort($refs);
 }

 #-- output
 foreach ($refs as $id=>$num) {
    echo "· "
       . "(<a href=\"" . ewiki_script("links", $id) . "\">$num</a>) "
       . ($exist[strtolower($id)]
           ? ("<a href=\"" . ewiki_script("", $id) . "\">$id</a>")
           : ("$id")  )
       . "<br>\n";
 }

?>