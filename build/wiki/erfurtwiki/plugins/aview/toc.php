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
   $n_last = 0;
   foreach ($src as $i=>$line) {

      if ($line[0] == "!") {
         $n = strspn($line, "!");
         if (($n <= 3) and ($line[$n]==" ")) {
             if ($n < $n_last) $add[0] = '</ol>';
             if ($n > $n_last) $add[1] = '<ol>';
            $text = substr($line, $n);
            $toc[$i] =  $add[0].$add[1] . /*str_repeat("&nbsp;", 3-$n) . "·"
                     .*/ '<li><a href="#line'.$i.'">'
                     . trim($text) . "</a></li>";

            $src[$i] = str_repeat("!", $n) . $text . " [#line$i]";
            $n_last = $n;
            $add = array('','');
         }
      }
   }
   
   // Also search MediaWiki headlines
   $n_last = 0;

   foreach ($src as $i=>$line) {
      if ($line[0] == "=" && $line[strlen($line)-1] == "=") {
         $n = strspn($line, "=");
         if (($n <= 3)) {
             if ($n < $n_last) $add[0] = '</ol>';
             if ($n > $n_last) $add[1] = '<ol>';
            $text = substr($line, $n,-$n);
            $toc[$i] = $add[0].$add[1] . /*str_repeat("&nbsp;", 2*($n)) . (($n == 3) ? '·': '')
                     . */'<li><a href="'.implode('/', PRequest::get()->request).'#line'.$i.'">'
                     . trim($text) . "</a></li>";

            $src[$i] = str_repeat("=", $n) . " [#line$i]" . $text . str_repeat("=", $n);
            $n_last = $n;
            $add = array('','');
         }
      }
   }

   $src = implode("\n", $src);
   $GLOBALS["ewiki_page_toc"] = &$toc;
}


#-- injects toc above page
function ewiki_toc_view_prepend(&$html) {

    global $ewiki_page_toc;
    $words = new MOD_words();
    $html_new = "<div class=\"page-toc\">\n";
    $html_new .= '
        <div class="page-toc-caption">'. $words->getFormatted('WikiPages') .'</div>
        <a href="wiki">'. $words->getFormatted('WikiFrontPage') .'</a><br />
        <a href="wiki/NewestPages">'. $words->getFormatted('WikiNewestPages') .'</a><br />
        <a href="wiki/WikiMarkup">'. $words->getFormatted('WikiMarkup') .'</a>

    ';
    
    if (count($ewiki_page_toc) >= 3) {
        $html_new .= ( EWIKI_TOC_CAPTION ? '<div class="page-toc-caption">'.ewiki_t("toc")."</div>\n" : '')
        . '<ol>'.implode("", $ewiki_page_toc) . '</ol>';
    }
   
    $html_new .= "</div>\n";
    $html_new .= str_replace('&lt;br/&gt;', "\n", $html); // Added by lupochen to remove all escaped BR-tags from the Page
    
    $html = $html_new;
   // $ewiki_page_toc = NULL;
}


?>