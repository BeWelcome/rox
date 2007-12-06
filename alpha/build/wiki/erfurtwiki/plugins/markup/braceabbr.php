<?php

/*
   As seen in »Textile«, this plugin provides markup for <abbr> using:
   "TEA(The Explained Abbreviaton)" in any page.
*/

$ewiki_plugins["format_source"][] = "ewiki_format_src_brace_abbr";

function ewiki_format_src_brace_abbr(&$src) {
   
   $src = preg_replace(
      "/\b([A-Z]{2,10})\s?\([^([\])]{5,50}\)/se", 
      " '<abbr title=\"~[' . urlencode(stripslashes('$2')) .
        ']\">$1</abbr>' ",
      $src
   );
}

?>