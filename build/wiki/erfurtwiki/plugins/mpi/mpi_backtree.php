<?php

/*
   This mpi provides a tree of page backlinks from the current or any
   given page. It's the opposite of mpi_localsitemap.

   <?plugin BackTree ?>
   <?plugin BackTree page=ForThatPage depth=2 ?>

   You shouldn't use depths greater than 3, else database walking would
   take a while, and the result would be uglily long.
*/

$ewiki_plugins["mpi"]["backtree"] = "mpi2_backtree";


function mpi2_backtree($action, &$args, &$iii, &$s) {

   ($depth = $args["depth"]) or ($depth = 2);
   if ($depth > 5) {
      $depth = 5;
   }
   ($id = $args["page"]) or ($id = $GLOBALS["ewiki_id"]);

   $src = mpi2_backtree_revbl($id, "", $depth);

   #-- throw output into _format() kernel buffer
   if ($src) {
      $c = &$iii[$s["in"]];
      $c[0] = $src;
      $c[1] = 0x00FF;
      $c[2] = "core";
   }
   return($src);
}


function mpi2_backtree_revbl($id, $li, $depth) {
   $src = "";
   $li .= "*";
   if ($depth--) {
      if ($refs = ewiki_get_backlinks($id)) {
         foreach ($refs as $id) {
            $src .= "$li [$id]\n";
            $src .= mpi2_backtree_revbl($id, $li, $depth);
         }
      }
   }
   return($src);
}

?>