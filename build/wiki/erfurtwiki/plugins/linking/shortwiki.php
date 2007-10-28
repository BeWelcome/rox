<?php
/*
   adds the markup extension to render  "User|Name"
   into a link to "UserName" entitled
   with just "User", or simply said: Short|Link ==> [Short|ShortLink]
*/

$ewiki_plugins["format_source"][] = "ewiki_markup_short_wiki_links";

function ewiki_markup_short_wiki_links(&$src) {

   $u = EWIKI_CHARS_U;
   $l = EWIKI_CHARS_L;
   
   $src = "[" . $src;
   $src = preg_replace(
      "/([\]\n][^\[]*?)([$u]+[$l]+[$u$l]*)\|([$u]+[$l]+[$u$l]*)/ms",
      '\1[\2|\2\3]',
      $src
   );
   $src = substr($src, 1);
}

?>