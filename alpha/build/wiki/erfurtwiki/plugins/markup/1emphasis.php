<?php

#  This plugin allows for the single word emphasis markup:
#      *bold*  /italic/  _underlined_
#


$ewiki_plugins["format_source"][] = "ewiki_format_single_word_emphasis";
function ewiki_format_single_word_emphasis(&$src) {
   $src = preg_replace("/\B\*(\w+)\*\B/", "<b>$1</b>", $src);
   $src = preg_replace("/\B\/(\w+)\/\B/", "<i>$1</i>", $src);
   $src = preg_replace("/\b\_([a-zA-Z\d\300-\377]+)\_\b/", "<u>$1</u>", $src);
}

?>