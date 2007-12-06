<?php

# this plugin is only utilized by WordIndex and PageIndex, but is
# in fact a ["list_pages"] plugin and could be used with others too
#
# links of the list are grouped into <table> blocks with the first
# letter as block title


$ewiki_plugins["list_dict"][0] = "ewiki_fancy_list_dict";
           // ["list_pages"][0] = ...



function ewiki_fancy_list_dict($links) {


   $o .= '<table border="0" cellpadding="3" cellspacing="2">' . "\n";

   $lfl = false;

   foreach ($links as $line) {

      $nfl = strtoupper(substr($line, strpos($line, ">") + 1));
      $nfl = strtr($nfl, "ÄÖÜß0123456789", "AOUS          ");
      while ((($nfl[0] < "A") || ($nfl[0] > "Z")) && ($nfl[0] != " ")) {
         $nfl = substr($nfl, 1);
      }
      $nfl = $nfl[0];

      if ($lfl != $nfl) {

         if ($lfl) {
            $o .= "</td></tr>\n";
         }

         $o .= '<tr><td valign="top" align="center" width="22" bgcolor="#333333" color="#eeeeee" class="darker reverse"><h2>' .
               ($lfl = $nfl) . '</h2></td>' .
               '<td valign="top">';
      }
      else {
         $o .= "<br />";
      }

      $o .= $line ;

   }

   $o .= "</td></tr>\n</table>\n";

   return($o);
}


?>