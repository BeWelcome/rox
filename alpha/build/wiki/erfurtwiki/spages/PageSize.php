<?php
/*
   this lists pages by size
*/

 echo ewiki_make_title($id, $id, 2);

 #-- fetch all pages
 $list = array();
 $result = ewiki_db::GETALL(array("content"));
 while ($r = $result->get(0, 0x0137, EWIKI_DB_F_TEXT)) {
    $list[$r["id"]] = strlen($r["content"]);
 }

 #-- beatify list
 if (isset($_REQUEST["desc"])) {
    asort($list);
 }
 else {
    arsort($list);
 }
 $r = array();
 foreach ($list as $id=>$size) {
    $r[] = array($id, "", "", "$size octets");
 }
 unset($list);

 #-- output
 echo ewiki_list_pages($r, 0);

?>