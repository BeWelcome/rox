<?php

/*
   This markup extension, allows for following syntax to merge
   neighboured table cells together:

   | col1 | col2 | col3 |
   | col2 || col2 and 3 |
   ||| this occoupies the whole row |
   | col1 | col2 | col3 |
*/


$ewiki_plugins["format_final"][] = "ewiki_table_colspan";
function ewiki_table_colspan(&$html) {
   $html = preg_replace(
      '#(<td></td>\s*)+<td#e',
      '"<td colspan=\"" . (1+((int)(strlen(preg_replace("/\s+/","","$0"))-3)/9)) . "\""',
      $html
   );
}

?>