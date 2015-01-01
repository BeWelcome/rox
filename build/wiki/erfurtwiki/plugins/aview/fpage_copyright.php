<?php
/*
    Generates a copyright notice.
    
    Created by: Jeffrey Engleman
    
*/

$ewiki_t["en"]["ALLRIGHTSRESERVED"] = "all rights reserved.";

$ewiki_plugins["page_final"][] = "ewiki_page_final_copyright";

function ewiki_page_final_copyright(&$o, &$id, &$data, &$action){
       $o.='<div id="copyright"><a rel="license" href="https://creativecommons.org/licenses/by-sa/3.0/"><img class="float_left" alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a>This work is licenced under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Licence</a>.</div>';
}

?>