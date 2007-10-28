<?php

/*
   <?plugin TableEditor
    |   aaaa   |   bbbb   |   cccc   |   eeee   |
    |   1...   |   2...   |   3...   |   4...   |
    |   ...    |   ...    |   ...    |   ...    |
   ?>

   Will render the table as usual, but provide a [TableEditor] button,
   which rerenders the table cells with lots of <textarea>s and another
   [Save] button for easier editing.

   While this feature was meant for large tables, it may not work out
   that well for some browsers - because they often aren't prepared for
   more than a few <textarea> and other <form> <input> fields.
*/


$ewiki_plugins["mpi"]["tableeditor"] = "ewiki_mpi_tableeditor";
function ewiki_mpi_tableeditor($action, $args, &$iii, &$s) {

   global $ewiki_id, $ewiki_data;

   #-- config
   $rel = 5/2;  // favoured ratio width to height
   $SEP = "|";  // table cell separator ("|", or "||" for other Wikis);
   $add_empty_row = 1;
   $w_min = 7;
   $w_stretch = 1.17;   // + 17%
   $w_max = 35;
   $h_min = 2;
   $h_max = 12;

   #-- analyze current table for cell sizes
   $t = array();
   foreach (explode("\n", trim($args["_"])) as $row) {
      $t[] = explode($SEP, trim(trim($row), $SEP));
   }
   $t_widths = array();
   $t_heights = array();
   $x = count($t[0]);
   $y = count($t);
   for ($row=0; $row<$y; $row++) {
      for ($col=0; $col<$x; $col++) {
         $len = strlen($t[$row][$col]);

         $w = sqrt($rel*$len);
         if ($w < $w_min) { 
            $w = $w_min;
         }
         $h = max((int) ($len/$w), $h_min);
         $w = (int) ($w * $w_stretch);
         $h = min($h, $h_max);
         $w = min($w, $w_max);

         $t_widths[$col] = max($t_widths[$col], $w);
         $t_heights[$row] = max($t_heights[$row], $h);
      }
   }


   #-- store -----------------------------------------------------------
   $o = '<div class="mpi TableEditor">';
   if ($_REQUEST["te_save"]) {

      $data = ewiki_db::GET($ewiki_id);
      if ($data && ($_REQUEST["te_d_ver"] == $data["version"])) {

         if (!preg_match_all('/<\?plugin:?\s*TableEditor/i', $data["content"], $uu)) {
            $o .= "Could not detect the exact position of the TableEditor inside the page. Not saved.<br />";
         }
         elseif (count($uu[0]) >= 2) {
            $o .= "There can only be <b>one</b> TableEditor call in a page!<br />";
         }
         else {
            $src = "";
            $t = $_REQUEST["te_d"];
            foreach ($t as $y=>$row) {
               $empty = 1;
               foreach ($row as $x=>$cell) {
                  $t[$y][$x] = trim(strtr($cell, "\r\n\t\f", "    "));
                  $empty = $empty && empty($t[$y][$x]);
               }
               if ($empty) {
                  unset($t[$y]);
                  continue;
               }
               $src .= "$SEP " . implode(" $SEP ", $t[$y]) . " $SEP\n";
            }

            $data["content"] = preg_replace(
               '/<\?plugin:?\s*TableEditor.+?\?>/is',
               "<?plugin TableEditor\n\n$src\n?>",
               $data["content"]
            );
            ewiki_data_update($data);
            $data["version"]++;

            ewiki_db::WRITE($data);
         }

      }
      else {
         $o .= ewiki_t("ERRVERSIONSAVE") . "<br />\n";
      }
   }


   #-- output start ----------------------------------------------------
   $o .= '<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" enctype="multipart/form-data">'
       . '<input type="hidden" name="te_d_ver" value="'.($ewiki_data["version"]).'">'
       . '<input type="hidden" name="id" value="'.htmlentities($ewiki_id).'">';

   #-- print <textarea> table variant
   if ($_REQUEST["te_load"]) {
      $o .= '<input type="submit" name="te_save" value="SaveTable"><br />';
      $o .= '<table border="1" cellspacing="1" cellpadding="2">';
      if ($add_empty_row) {
         $y++;
      }
      for ($row=0; $row<$y; $row++) {
         for ($col=0; $col<$x; $col++) {
            $t[$row][$col] = "<textarea style=\"border:none;background:transparent;\" name=\"te_d[$row][$col]\" cols=\"$t_widths[$col]\" rows=\"$t_heights[$row]\" wrap=\"soft\">"
                           . htmlentities(trim($t[$row][$col]))
                           . "</textarea>";
         }
         $o .= '<tr><td>' . implode('</td><td>', $t[$row]) . '</td></tr>' . "\n";
      }
      $o .= "</table>\n";
   }

   #-- reconvert into WikiMarkup, and insert into $iii
   else {
      #-- insert <html> form at current position
      $o .= '<input type="submit" name="te_load" value="TableEditor"><br />';
      $in = $s["in"];
      $iii[$in][0] = "WILL BE REPLACED with \$o...";

      #-- mk table markup, insert into $iii
      $src = "\n\n";
      foreach ($t as $row) {
         $src .= "$SEP " . implode(" $SEP ", $row) . " $SEP\n";
      }
      $src .= "\n";
      $iii = array_merge(
         array_slice($iii, 0, $in+1),
         array(array($src, 0x137F, "core")),
         array_slice($iii, $in+1)
      );

      // the following return($o); will insert the <form> into
      // the current input buffer $iii[$in][0] later
   }

   $o .= "</form></div>\n";
   return($o);
}

?>