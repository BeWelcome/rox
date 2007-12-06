<?php

/*
   -- OBSOLETED by according spages/ plugin --
   this sums up all the hits from all pages, and prints the
   overall score points
*/



$ewiki_plugins["page"]["HitCounter"] = "ewiki_page_hitcounter";


function ewiki_page_hitcounter($id, $data, $action) {

   #-- loop thur all pages, and mk sum
   $n = 0;
   $result = ewiki_db::GETALL(array("hits"));
   while ($r = $result->get()) {
      if ($r["flags"] & EWIKI_DB_F_TEXT) {
        $n += $r["hits"];
      }
   }

   #-- output
   $title = ewiki_make_title($id, $id, 2);
   $AllPages = '<a href="'. ewiki_script("", "PageIndex") .'">AllPages</a>';
   $o = <<< ___
$title
The overall hit score of $AllPages is:
<div class="counter">
  $n
</div>
___
   ;
   return($o);
}


?>