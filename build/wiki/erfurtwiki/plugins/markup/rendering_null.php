<?php

 # this plugin replaces the wiki rendering core with almost nothing, and
 # thus allows you to put plain HTML into all pages
 # (WikiWords however, will still render as usual)



$ewiki_plugins["render"][0] = "ewiki_format_null";



function ewiki_format_null ($content, $scan_links=1, $HTML=1, $HTML=1) {

   global $ewiki_plugins, $ewiki_links;

   $link_regex = "#[!~]?(
\[[^<>[\]\n]+\] |
\^[-".EWIKI_CHARS_U.EWIKI_CHARS_L."]{3,} |
(?:[".EWIKI_CHARS_U."]+[".EWIKI_CHARS_L."]+){2,}[\w\d]*(:[\w\d]{3,})?
)#";
   $link_regex = str_replace(" ", "", strtr($link_regex, "\n", " "));
   
   $content = preg_replace_callback($link_regex, "ewiki_link_regex_callback", $content);

   #-- call post processing plugins
   foreach (@$ewiki_plugins["format_final"] as $pf) $pf($content);

   return($content);

}


?>