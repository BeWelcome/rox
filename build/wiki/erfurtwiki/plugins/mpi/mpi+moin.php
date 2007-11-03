<?php

/*
   MoinMoin-syntax for plugin activation also interfers with linking
   sometimes, so you should enable this plugin only if you really
   need it. As it is internally rewritten to mpi markup, you can use
   all available plugins, but the ones expecting long arguments.

     [[PluginName()][]  - does never work without ()
     [[Plugin(arg1=val1, arg2=val2)]]
*/


$ewiki_plugins["format_source"][] = "ewiki_mpi_moin_syntax";

function ewiki_mpi_moin_syntax(&$src) {
   $src = preg_replace(
     '/\[\[(\w+)\([^)]*\)([^\(\)\[\]]+)*\]\]/imse',
     '"<?plugin $1 " .
      stripslashes("$2")
      ($3 ? stripslashes(" $3 ") : "") .
      " ?>"     
     ', $src
   );
}


?>