<?php

/*
   this plugin converts the PhpWiki1.2 [[NoLink] to ErfurtWiki ![NoLink]
   (fastly)
*/


$ewiki_plugins["format_source"][] = "ewiki_format_source_emulate_phpwiki12";


function ewiki_format_source_emulate_phpwiki12(&$source) {
   $source = str_replace("[[", "![", $source);
}


?>