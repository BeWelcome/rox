<?php

# this was the original page list generation code using <ul> and <li> tags,
# which however annoyed some people and has therefor been replaced in the
# core, and is now banned into this plugin


$ewiki_plugins["list_pages"][0] = "ewiki_list_pages_ul";


function ewiki_list_pages_ul($lines=array(), $list_tag="ul") {

   $o = "<" . $list_tag . ">\n";
   $o .= "<li>" . implode("</li>\n<li>", $lines) . "</li>\n";
   $o .= "</" . $list_tag . ">\n";

   return($o);
}


?>