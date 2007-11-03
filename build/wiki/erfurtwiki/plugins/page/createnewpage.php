<?php

/*
   This plugin now is completely superfluous, but a convinience function
   often requested for. See also page_addnewpage for the advanced version.
   (It may fail for uncommon PHP "variables_order" settings.)
*/


$ewiki_plugins["page"]["CreateNewPage"] = "ewiki_createpage";

$ewiki_t["de"]["name of the new page"] = "Name der neuen Seite";
$ewiki_t["de"]["create"] = "erstellen";


function ewiki_createpage($id, &$data, $version) {

   $o = ewiki_make_title($id, $id, 2);

   #-- output page creation dialog
   $o .= ewiki_t(
      '<form action="'.ewiki_script("","").'" method="POST" enctype="multipart/formdata"> '
      .'_{name of the new page} <input type="text" name="id" size="26" value="">'
      .'<br />'
      .'<input type="submit" value="_{create}">'
      .'</form>'
   );
   return($o);
}

?>