<?php

/*
   shows up pages that don't link to anywhere
*/

#-- title
echo ewiki_make_title($id, $id, 2);
echo "\n<p>Pages without links to anywhere else. Often this are unwanted ShallowPages.</p>\n";

#-- find / analyze db
$result = ewiki_db::GETALL(array("refs"));
$ll = array();
while ($row = $result->get(0, 0x0137, EWIKI_DB_F_TEXT))
{
   $id = $row["id"];
   $refs = trim($row["refs"]);
   
   #-- no links to anywhere else?
   if ((!$refs) || (strtolower($refs) == strtolower($id))) {
      $ll[] = $id;
   }
}

#-- bring up list
echo ewiki_list_pages($ll, 0, 0, 0);

?>