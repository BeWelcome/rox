<?php

 # this plugin prints out tables instead of <ul> lists for all the
 # internally generated pages
 # of course you should customize THIS!


 $ewiki_plugins["list_pages"][] = "ewiki_list_pages_fancy1";


 function ewiki_list_pages_fancy1($lines) {

    $cell_attr = 'bgcolor="#999999"';

    $o = '<table border="0" cellpadding="2" cellspacing="2" width="90%">' . "\n";
    $o .= "<tr><td $cell_attr>" . implode("</td></tr>\n<tr><td $cell_attr>", $lines) . "</td></tr>\n";
    $o .= "</table>\n";
    return($o);
 }


?>