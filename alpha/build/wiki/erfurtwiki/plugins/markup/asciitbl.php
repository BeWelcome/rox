<?php

/*
   ASCII-Art tables can be used in Wiki pages, if you load this plugin (it
   internally converts them into standard tables).
   Such tables usually look like:

   +----------+--------+----+
   | dl       | 0x0A   | 1  |
   +----------+--------+----+
   | ibbo,    | 0x12   | 2  |
   | nna      |        |    |
   +----------+--------+----+
   | nf,      | 0xFF   | 3  |
   +----------+--------+----+

   It's essentially a list, which rows are separated by horizontal bars, so
   one can have multiple lines making up one cell. If you don't import such 
   tables from an app (mysql outputs such tables), you could shorten writing
   them into:

   --------
   | cell1   | cell2 |
   ------
   | row2, col1 |  col2/cell4  |
   | still row2 |  ...  |
   +-----
   | row 3   | ... |
   -------

   Instead of only using minus signs, you could have some plus signs in it
   (or even a complete line of them).
*/



$ewiki_plugins["format_source"][] = "ewiki_formatsrc_ascii_art_tables";



function ewiki_formatsrc_ascii_art_tables(&$src) {
   $src = preg_replace('/^([+-]{5,}\n\|[^\n]+\n((\|[^\n]+|[+-]+)\n)+)/mse', 'ewiki_formatsrc_asciitbl_cells(stripslashes("\\1"))', $src);
}


function ewiki_formatsrc_asciitbl_cells($str) {
   $rows = preg_split('/^[+-]+\n/m', $str);
   $str = "";
   foreach ($rows as $row) {
      if (empty($row)) {
         continue;
      }
      $cells = array();
      $lines = explode("\n", $row);
      foreach ($lines as $l=>$line) {
         $add = explode("|", trim($line, "|"));
         if (empty($cells)) {
            $cells = $add;
         }
         else {
            foreach ($add as $i=>$text) {
               if (!trim($text) && ($l+1<count($lines))) { 
                  $text = "<br /><br />";
               }
               $cells[$i] .= " $text";
            }
         }
      }
      $str .= "|" . implode("|", $cells) . "|\n";
   }
   $str = preg_replace('/(<br />\s*)+\|/', "|", $str);
   return($str);
}


?>