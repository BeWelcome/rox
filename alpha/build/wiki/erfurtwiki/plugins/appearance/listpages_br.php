<?php

 # this plugin prints out <br /> - separated lists
 # Carsten Senf <ewiki@csenf.de>


 $ewiki_plugins["list_pages"][] = "ewiki_list_pages_fancy3";


 function ewiki_list_pages_fancy3($lines) {

    return join("<br />", $lines);
 }


?>