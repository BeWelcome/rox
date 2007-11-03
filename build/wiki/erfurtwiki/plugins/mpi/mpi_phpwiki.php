<?php

/*
   fake PhpWiki plugins
   ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
   following stuff mimics some of the plugins found in PhpWiki v1.3,
   to make use of them you must first explicitly include() this plugin
   (as opposed to all real mpi plugins).

   Unlike the others, you must include() this mpi plugin on its own,
   because it has a file name which does not match the plugin name(s).
*/


#-- glue
$ewiki_plugins["mpi"]["allusers"]	= "ewiki_mpi_phpwiki_allusers";
$ewiki_plugins["mpi"]["allauthors"]	= "ewiki_mpi_phpwiki_allusers";
$ewiki_plugins["mpi"]["allpages"]	= "ewiki_format_mpi_phpwiki_allpages";




#-- mimics phpwiki AllUsers plugin ----------------------------------------
# - fetches user names from the 'author' column of all pages,
#   but not from a user registry
function ewiki_mpi_phpwiki_allusers($action, &$args, &$iii, &$s) {
   $authors = array();

   $result = ewiki_db::GETALL(array("author"));
   while ($row = $result->get()) {
      if ($uu = strtok($row["author"], "(")) {
         $authors[trim($uu)] = 1;
      }
   }
   $authors = array_keys($authors);
   natcasesort($authors);

   foreach ($authors as $a) {
      $o .= "· " . ewiki_link_regex_callback(array($a)) . "<br />\n";
   }
   return($o);
}




#-- mimics the phpwiki "AllPages" plugin --------------------------------
function ewiki_format_mpi_phpwiki_allpages($action, &$args, &$iii, &$s) {
   if ($action=="html") {
      return(ewiki_page_index(0,0,0,$args));
   }
}



?>