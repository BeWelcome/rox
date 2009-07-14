<?php

/*
   Adds a CSS container with links to all listed headlines of the
   current page (but threshold for its activation is 3).

    .wiki .page-toc {
       width: 160px;
       float: right;
       border: 2px #333333 solid;
       background: #777777;
    }

   Modified 20040810 by Jochen
   - makes now use of EWIKI_TOC_CAPTION to print "TOC" above it all
   - indention swapped (biggest headlines are now left,
     and smaller ones are indented to the right)
   - added some \n for more readable html
*/


#-- reg
$ewiki_plugins["format_source"][] = "ewiki_toc_format_source";
$ewiki_plugins["format_final"][] = "ewiki_toc_view_prepend";
define("EWIKI_TOC_CAPTION", 3);
$ewiki_t["en"]["toc"] = "Content";
$ewiki_t["de"]["toc"] = "Inhalt";


#-- wiki page source rewriting
function ewiki_toc_format_source(&$src) {

   $toc = array();

   $src = explode("\n", $src);
   foreach ($src as $i=>$line) {

      if ($line[0] == "!") {
         $n = strspn($line, "!");
         if (($n <= 3) and ($line[$n]==" ")) {

            $text = substr($line, $n);
            $toc[$i] = str_repeat("&nbsp;", 3-$n) . "·"
                     . '<a href="#line'.$i.'">'
                     . trim($text) . "</a>";

            $src[$i] = str_repeat("!", $n) . $text . " [#line$i]";

         }
      }
   }
   // Also search MediaWiki headlines
   foreach ($src as $i=>$line) {
      if ($line[0] == "=" && $line[count($line)] == "=") {
         $n = strspn($line, "=");
         if (($n <= 3) and ($line[$n]==" ")) {

            $text = substr($line, $n,-$n);
            $toc[$i] = str_repeat("&nbsp;", 2*($n)) . (($n == 3) ? '·': '')
                     . '<a href="'.implode('/', PRequest::get()->request).'#line'.$i.'">'
                     . trim($text) . "</a>";

            $src[$i] = str_repeat("=", $n) . " [#line$i]" . $text . str_repeat("=", $n);

         }
      }
   }

   $src = implode("\n", $src);
   $GLOBALS["ewiki_page_toc"] = &$toc;
}


#-- injects toc above page
function ewiki_toc_view_prepend(&$html) {

   global $ewiki_page_toc;

   if (count($ewiki_page_toc) >= 3) {

      $html = "<div class=\"page-toc\">\n"
         . ( EWIKI_TOC_CAPTION ? '<div class="page-toc-caption">'.ewiki_t("toc")."</div>\n" : '')
         . implode("<br />\n", $ewiki_page_toc) . "</div>\n"
         . str_replace('&lt;br/&gt;', "\n", $html); // Added by lupochen to remove all escaped BR-tags from the Page
   }

   // $ewiki_page_toc = NULL;
}


?>