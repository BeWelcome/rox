<?php

/*
   Prints out /tmp/ewiki.log for debugging
*/
/* 
* @author alex wan <alex@burgiss.com>
* @author andy fundinger <andy@burgiss.com> (Minor contributions and maintenance)
*/

$ewiki_plugins["page"]["EWikiLog"] = "ewiki_page_ewikilog";

function ewiki_page_ewikilog($id, $data, $action) 
{
    ob_start();
    echo ewiki_make_title($id, $id, 2);
    echo '<pre>';
    readfile(EWIKI_LOGFILE);
    echo '</pre>';
    $o = ob_get_contents();
    ob_end_clean();

    return($o);
}



?>