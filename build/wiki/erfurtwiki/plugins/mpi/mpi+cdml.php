<?php

/*
   This plugin adds the "CDML"-Syntax (as found in ProWiki) to the
   mpi framework - you must load mpi.php also to make this work! Then
   you could write

     [[PluginName][arg1=val1][arg2=...]
      ... more text here
     ]

   instead of the regular mpi <?plugin PluginName ... ?> syntax.
*/


$ewiki_plugins["format_source"][] = "ewiki_mpi_cdml_syntax";

function ewiki_mpi_cdml_syntax(&$src) {
   $src = preg_replace(
     '/\[\[(\w+)\]([^\[\]]*)(\[\w+=[^\]]+?\])*([^\]]+)\]/imse',
     '"<?plugin " . "$1" .
      ($2 ? stripslashes(" $2 ") : "") .
      ($3 ? strtr(stripslashes("$3"), "[]", "  ") : "") .
      ($4 ? stripslashes(" $4") : "") .
      " ?>"     
     ', $src
   );
}


?>