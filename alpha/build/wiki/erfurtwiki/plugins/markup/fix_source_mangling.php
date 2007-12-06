<?php

/*
   Multiple other markup plugins implement features by running a regex
   on the whole source of a Wiki page. This may sometimes interfer with
   content inside page blocks (like <code> or <html>).
   If this plugin however is loaded, all old-style markup plugins instead
   run on the Wiki text page parts only, and further won't clash in other
   parts. This may be little slower.

   include() this plugin AFTER any markup plugins, which you think may
   cause problems:
      ...
      include("plugins/markup/foreign_stuff.php");
      include("plugins/markup/an_older_plugin.php");
      ...
      include("plugins/markup/fix_source_mangling.php");
      ...
   Plugins, which you believe won't cause harm to a wiki pages source
   ares, can be loaded AFTER _this_ plugin.
*/


#-- store current ["format_source"] plugin list, and clean it up
$ewiki_plugins["singleblock_fmt_src"] = $ewiki_plugins["format_source"];
$ewiki_plugins["format_source"] = array();

#-- register ours instead (for all real Wiki text blocks)
$ewiki_plugins["block"]["core"][] = "ewiki_format_block_wiki_text_source_mangling";


#-- calls source code mangling plugins on current block
function ewiki_format_block_wiki_text_source_mangling(&$c, &$in, &$ooo, &$s) {

   global $ewiki_plugins;

   if ($pf_a = $ewiki_plugins["singleblock_fmt_src"]) {
      foreach ($pf_a as $pf) {
         $pf($c);
   }  }
}


?>