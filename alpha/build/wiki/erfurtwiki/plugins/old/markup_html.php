<?php

/*
   If you happened to use the | at a start of a line to escape for
   <html> usage, then use this plugin to keep compatible for a while.
   Nowadays, you enclose blocks or fragments into <html></html> or
   even <htm></htm> blocks (the latter still allows WikiLinks and markup).
*/


$ewiki_plugins["format_source"][] = "ewiki_fsrc_old_html";

function ewiki_fsrc_old_html(&$src) {
   $src = preg_replace('/^\|(.+(\n\|.+)*\n)/me', '"<html>" . str_replace("\n|", "\n", stripslashes("\\1")) . "<html>"', $src);
}

?>