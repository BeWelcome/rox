<?php

/*
   Emulates UseMode like headlines (but others use this too).
   == Heading ==      corresponds to !!! Large Heading
   === Heading ===    corresponds to !! Meadium
   ==== Heading ====  corresponds to ! Smallest Headline
   === # anchored headline ===

   And then also allows for:
   <toc>
*/


$ewiki_plugins["format_source"][] = "ewiki_format_src_usemod_headings";


function ewiki_format_src_usemod_headings(&$src) {

   global $usemod_toc;

   #-- ===Headlines===
   $src = preg_replace('/^(={2,4})([^=].*?)==+\s*$/me',
   '
        str_repeat("!", $we = (5 - strlen("\\1"))  ) . " "
      . (($uu=stripslashes(trim("\\2"))) && ($uu[0]=="#")
           ? ($GLOBALS["usemod_toc"][][$we]=$uu=trim(substr($uu, 1)))
             . (" #[" . preg_replace("/[^\w]/", "_", $uu) . "]")
           : ($uu)
        )
      . "\n\n"
   ', $src);

   #-- <toc>
   if (strpos($src, "&lt;toc&gt;")) {
      foreach ($usemod_toc as $i=>$d) {
         foreach ($d as $num=>$str) {
            $toc_str .= str_repeat("*", 4 - $num) . " [.#$str \"$str\"] \n";
         }
      }
      $src = preg_replace("'&lt;toc&gt;'", $toc_str, $src);
   }
}


?>