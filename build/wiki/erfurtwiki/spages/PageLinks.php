<?php
/*
   orders pages by count of links they have
*/

 echo ewiki_make_title($id, $id, 2);

 #-- fetch all pages
 $list = array();
 $result = ewiki_db::GETALL(array("refs"));
 while ($r = $result->get(0, 0x0137, EWIKI_DB_F_TEXT)) {
    $list[$r["id"]] = count(explode("\n", trim($r["refs"])));
 }

 #-- beatify list
 if (isset($_REQUEST["desc"])) {
    asort($list);
 }
 else {
    arsort($list);
 }
 $r = array();
 foreach ($list as $id=>$num) {
    $r[] = array($id, "", "", "($num)");
 }
 unset($list);

 #-- output
 echo ewiki_list_pages($r, 0);

?>