<?php

/*
   lists pages which are referenced but not yet written
   (AKA "DanglingSymlinks")
*/

 #-- title
 echo ewiki_make_title($id, $id, 2);

 #-- collect referenced pages
 $result = ewiki_db::GETALL(array("refs"));
 while ($row = $result->get()) {
     if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($row["id"], $uu, "view")) {
         continue;
     }   
     $refs .= $row["refs"];
 }

 #-- build array
 $refs = array_unique(explode("\n", $refs));

 #-- strip existing pages from array
 $refs = ewiki_db::FIND($refs);
 foreach ($refs as $id=>$exists) {
    if (!$exists && !strstr($id, "://") && strlen(trim($id))) {
       $wanted[] = $id;
    }
 }

 #-- print out
 echo "<ul>\n";
 foreach ($wanted as $page) {

    echo "  <li>" . ewiki_link($page) . "</li>\n";

 }
 echo "<ul>\n";


?>