<?php
/*
   this sums up all the hits from all pages, and prints the
   overall score points
*/

 echo ewiki_make_title($id, $id, 2);

 #-- loop thur all pages, and mk sum
 $n = 0;
 $result = ewiki_db::GETALL(array("hits"));
 while ($r = $result->get()) {
    if ($r["flags"] & EWIKI_DB_F_TEXT) {
      $n += $r["hits"];
    }
 }

 #-- output
 $AllPages = '<a href="'. ewiki_script("", "PageIndex") .'">AllPages</a>';
 echo <<< EOT
$title
The overall hit score of $AllPages is:
<div class="counter">
  $n
</div>
EOT;

?>