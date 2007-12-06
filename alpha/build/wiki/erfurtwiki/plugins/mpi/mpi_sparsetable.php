<?php

/*
   SparseTable allows you to build up a table in a data oriented
   fashion:

    <?plugin SparseTable columns="id, 2,p, a, b, c,d, e"

       id=identifier
         2=second
         p=points
         a=notes
         b=another column

       id=row2
         b=more text
         d=here is an entry

       id=row3
         p=...
         b=...
    ?>

   You could also assign a list of rows="..." instead of columns= names.
   In both cases you separate the individual row/column entries with an
   empty line, and use one of the specified identifiers and a "=" sign or
   ":" colon to fill the table cells with data.
   The row/column identifiers itself won't be printed and should therefore
   be choosen like variables. The first block of assignments then was
   typically used to make appropriate headings.

   (This plugin resembles a feature as seen in ProWiki, or at least
   tries to. You could get the ProWiki CDML syntax with an mpi framework
   extension plugin.)
*/


$ewiki_plugins["mpi"]["sparsetable"] = "ewiki_mpi_sparsetable";
function ewiki_mpi_sparsetable($action, $args, &$iii, &$s) {

   $SEP = "|";
   $o = "";
   $i = array();
   $t = array();

   #-- column/row names
   $use_rows = isset($args["rows"]);
   if ($use_rows) {
      $i = $args["rows"];
   }
   else {
      $i = $args["columns"];
   }
   $ind = preg_split("/\s*[:;,|]\s*/", strtolower(trim($i)));
   if (!$ind) {
      return("Note: use rows= or columns= for SparseTable");
   }
   $ind = array_flip($ind);
   $empty_line = array();
   for ($n=0; $n<count($ind); $n++) {
      $empty_line[$n] = "";
   }

   #-- input chunks
   $data = substr($args["_"], strpos($args["_"], "\n"));
   $data = preg_split("/\n\s*\n/", $data);
   unset($args);

   #-- walk through rows/cols
   foreach ($data as $block) {
      if (!trim($block)) {
         continue;
      }
      $d = preg_split('/^\s*(\w+)\s*[:=]+/m', $block, -1, PREG_SPLIT_DELIM_CAPTURE);

      $l = $empty_line;
      for ($i=1; $i<count($d); $i++) {
         $rname = strtolower(strtok(trim($d[$i]), " :=\t"));
         $val = trim($d[++$i]);
         $l[ $ind[$rname] ] = $val;
      }
      $t[] = $l;
   }

   #-- generate table
   foreach ($t as $line) {
      $o .= "$SEP " . implode(" $SEP ", $line) . " $SEP\n";
   }

   #-- put output back into $iii
   $iii[$s["in"]] = array(
      $o,
      0x007F,
      "core",
   );
   return($o);
}


?>