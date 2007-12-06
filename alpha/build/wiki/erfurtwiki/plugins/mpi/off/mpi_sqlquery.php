<?php

/*
   Use like: <?plugin SqlQuery  SELECT * FROM table2;  ?>
   What will yield a table dump of the selected result sets.

   You must first enable this plugin - create a symlink into the
   parent dir (it isn't found here in the off/ subdir).
*/

$ewiki_plugins["mpi"]["sqlquery"] = "ewiki_mpi_sqlquery";


function ewiki_mpi_sqlquery($action, &$args, &$iii, &$s) {

   #-- select PHP db funcs
   if (function_exists("anydb_query")) {
      $SQL_QUERY = "anydb_query";
      $SQL_FETCH = "anydb_fetch_array";
   }
   else {
      $SQL_QUERY = "mysql_query";
      $SQL_FETCH = "mysql_fetch_array";
   }

   #-- query
   if ($query = $args["_"]) {

      #-- security check
      if (!preg_match('/^\s*(SELECT|SHOW)\s+/i', $query, $uu)) {
         return("SQL query rejected");
      }

      $result = $SQL_QUERY($query);
      if (!$result) {
         return("failed SQL query");
      }

      #-- fetch data
      $r = array();
      while($row = $SQL_FETCH($result)) {
         foreach ($row as $i=>$d) {
            if (is_int($i)) {
               unset($row[$i]);
            }
         }
         $r[] = $row;
      }

      #-- output
      $o .= '<table border="1" cellpadding="2" cellspacing="0" class="sql-query">';
      $o .= '<tr><th>' . implode('</th><th>',array_keys($r[0])) . '</th></tr>'."\n";
      $alt = 0;
      foreach ($r as $row) {
         $alt = ($alt++) % 2;
         $add = $alt ? ' class="alternate"' : '';
         $o .= "<tr><td$add>" . implode("</td><td$add>",$row) . '</td></tr>'."\n";
      }
      $o .= "</table>\n";

      return($o);
   }

   return(mysql_error());
}

?>