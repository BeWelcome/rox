<?php

/*
   This markup extension, allows for following syntax to merge
   neigboured table cells together:

   | col1 | row2 | row3 |
   | col2 || row2 and 3 |
   ||| this occoupies the whole column |
   | row1 | row2 | row3 |
*/


$ewiki_plugins["format_final"][] = "evil_table_rowspan";
function evil_table_rowspan(&$html) {
   $html = preg_replace(
      '#(<td></td>\s*)+<td#e',
      '"<td rowspan=\"" . (1+(int)(strlen("$1")/9)) . "\">"',
      $html
   );
}

?>