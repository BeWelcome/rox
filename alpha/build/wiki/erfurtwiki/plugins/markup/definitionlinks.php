<?php

/*
   Turns definition list titles into anchors and all occourences
   of the terms into links to there.
*/

$ewiki_plugins["format_final"][] = "ewiki_format_final_deflistanchors";
function ewiki_format_final_deflistanchors(&$html) {
   $words = array();
   $html = preg_replace(
      '#<dt>([_\w\s]+)</dt>#me',
      '"<dt><a name=\"" . strtr($words[]="$1", " ", "_") . "\">$1</a></dt>"',
      $html
   );
   if ($words) {
      $html = preg_replace(
         '#(?<!>)(' . implode("|", $words) . ')(?![<"])#e',
         '"<a href=\"#" . strtr("$1", " ", "_") . "\">$1</a>"',
         $html
      );
   }
}

?>