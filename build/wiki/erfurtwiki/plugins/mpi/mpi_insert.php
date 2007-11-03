<?php

/*
   This mpi allows you to insert another wikipage into the current
   one using <?plugin Insert ThisWikiPage ?>. You can also temporarily
   change some rendering parameters, by supplying them as optional
   parameters:
     <?plugins insert PageName split_title=0 control_line=0 ?>

   The table=0 parameter would disable the optional table+border
   around the inserted page:
     <?plugins insert PageName table=0 ?>

   Please note, that the inserted page will be requested through
   an "sub-request" with ewiki_page(), thus usually incorporating
   all settings from the main page.
   
   As additional extension, you can have a split view (vertical) with
   multiple pages:
     <?plugins insert PageOne PageTwo  ?>
*/

  # you can disable the <table> generation, if you style pages via CSS
define("EWIKI_MPI_INSERT_TBL", 1);


$ewiki_plugins["mpi"]["insert"] = "ewiki_mpi_insert";
$ewiki_config["mpi_insert"] = array(
   "table" => EWIKI_MPI_INSERT_TBL,
);


function ewiki_mpi_insert($action="html", $args, &$iii, &$s) {

   global $ewiki_config;

   #-- save environment
   $save = array(
      "id", "config", "title", "ring", "author",
   );
   unset($prevG);
   $prevG = array();
   foreach ($save as $name) {
      $prevG["$name"] = $GLOBALS["ewiki_$name"];
   }

   #-- use any params as _config settings
   $args = $args + $ewiki_config["mpi_insert"];
   foreach ($args as $set=>$val) {
      if ($set != "_") { 
         $ewiki_config[$set] = $val;
      }
   }

   #-- render requested page, through sub-request
   $o = array();
   $o[] = ewiki_page($args["id"]);
   for ($n=1; $n<=10; $n++) {
      if ($id = $args[$n]) {
         $o[] = ewiki_page($id);
      }
   }

   #-- reset env
   foreach ($save as $name) {
      $GLOBALS["ewiki_$name"] = $prevG[$name];
   }

   #-- mk table around output
   $on = count($o);
   if ($args["table"] || ($on >= 2)) {
      $o = implode("</td>\n<td valign=\"top\">", $o);
      $o = '<table border="'.$args["table"].'" cellpadding="5" cellspacing="5">'
      // . '<colgroups>' . str_repeat('<col width="'.((int)(100/$on)).'%" />', $on) . '</colgroups>'
         . '<tr><td valign="top">' . $o . '</td></tr></table>';
   }
   else {
      $o = implode("\n<br /><!-- cut-here --><br />\n", $o);
   }
   $o = '<div class="mpi-insert">' . $o . '</div>';

   return($o);
}

?>