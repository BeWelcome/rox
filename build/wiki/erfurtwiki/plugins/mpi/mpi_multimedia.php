<?php

/*
  this mpi allows to embed multimedia files using following syntax:
  <?plugin MultiMedia http://www.example.com/movie.swf ?>
    or
  <?plugin MultiMedia href="http://..." ?>
*/


$ewiki_plugins["mpi"]["multimedia"] = "ewiki_mpi_multimedia";


function ewiki_mpi_multimedia($action="html", $args, &$iii, &$s) {

   switch ($action) {
      case "doc": return("The <b>multimedia</b> plugin allows to reference multimedia objects which are no plain images (like videos, flash, applets).");
      case "desc": return("reference multimedia files");

      default:
         $a_url = array("href", 0, "url", "src");
         $a_std = array("width", "height", "type");
         $a_forb = array_merge(array("_"), $a_url, $a_std);

         #-- href
         foreach ($a_url as $i) {
            if ($href = $args[$i]) {
               break;
         }  }

         #-- <object> tag, std args
         $o .= '<object data="' . $href . '"';
         foreach ($a_std as $i) {
            if ($v = $args[$i]) {
               $o .= " $i=\"" . htmlentitites($v) . '"';
            }
         }
         $o .= '>';

         #-- <param> args
         foreach ($args as $i=>$v) {
            if (!in_array($i, $a_forb)) {
               $o .= '<param name="'.htmlentities($i).'" value="'.htmlentities($v).'">';
            }
         }
         $o .= "Your browser cannot view this multimedia object.";
#<off>#  $o .= '<embed src="' . $href . '"></embed>';
         $o .= "</object>";
   }

   return($o);
}

?>