<?php

/*
   adds some CSS to "beautify" the wiki page output,
   many things can be configured inside of the code generation function;
   the idea for this were brought from http://meyerweb.com/ and the original
   'wella' text filter script
*/


$ewiki_plugins["view_final"][] = "ewiki_view_final_fun_wella";


function ewiki_view_final_fun_wella(&$html, $id, $data, $action) {

   #-- configuration
   $where = "both";		// both, left, right, none
   $width = 60;			// actually only the half of the used width
   $start = 0.25 * M_PI;	// where sin() starts
   $length = 3 * M_PI;		// how much indentation to produce
   $dx = M_PI / 32;		// calculation step width

   $o = "";
   for ($x=$start; $x<$length; $x+=$dx) {

      $n = (int) ($width + $width * sin($x));

      switch($where) {

         case "both":
           $o .= '<span style="float:left;clear:left;width:'.$n.'px;">&nbsp;</span>'.
                 '<span style="float:right;clear:right;width:'.(2*$width-$n).'px;">&nbsp;</span>';
           break;

         default:
           $o .= '<span style="float:' . $where . ';clear:' . $where .
                 ';width:' . $n . 'px;">&nbsp;</span>';
           break;
      }
   }

   $o .= '<br style="display:none;">';
   $html = "$o$html";
}


?>