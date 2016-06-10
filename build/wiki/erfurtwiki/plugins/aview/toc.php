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
   
   Modified 20090717 by Micha (bw: lupochen)
   - added quite some own functionality
*/


#-- reg
$ewiki_plugins["format_source"][] = "ewiki_toc_format_source";
$ewiki_plugins["view_final"][] = "ewiki_toc_view_prepend"; //show on every page

define("EWIKI_TOC_CAPTION", 3);
$ewiki_t["en"]["toc"] = "Content";
// Toc caption showed always German, even if I was reading/surfing site in English. crumbking
// $ewiki_t["de"]["toc"] = "Inhalt";

#-- wiki page source rewriting
function ewiki_toc_format_source(&$src) {

   $toc = array();

   $src = explode("\n", $src);
   $found = false;
   for ($ii = 0; $ii < count($src); $ii++) {   
       if (stripos($src[$ii],"[TOC]") === 0) {
           $src[$ii] = str_replace("[TOC]", "", $src[$ii]);
           $found = true;
           break;
       }
   }
   
   /* Don't make use of ErfurtWiki headlines for now */
   // $n_last = 0;
   // foreach ($src as $i=>$line) {
   // 
   //    if ($line[0] == "!") {
   //       $n = strspn($line, "!");
   //       if (($n <= 3) and ($line[$n]==" ")) {
   //           if ($n < $n_last) $add[0] = '</ol>';
   //           if ($n > $n_last && $n_last != 0) $add[1] = '<ol>';
   //          $text = substr($line, $n);
   //          $toc[$i] =  $add[0].$add[1] . /*str_repeat("&nbsp;", 3-$n) . "·"
   //              . */'<li><a href="'.implode('/', PRequest::get()->request).'#line'.$i.'">'
   //                   . trim($text) . "</a></li>";
   // 
   //          $src[$i] = str_repeat("!", $n) . $text . " [#line$i]";
   //          $n_last = $n;
   //          $add = array('','');
   //       }
   //    }
   // }
   
   // Also search MediaWiki headlines
   $n_last = 0;
   $n_number = 1;
   $iii = 1;
   if ($found) {
   foreach ($src as $i=>$line) {

      if ($line[0] == "=" && $line[strlen($line)-1] == "=") {
         $n = strspn($line, "=");
         if (($n <= 3)) {
             if ($n < $n_last) {
                 $n_number = (strrpos($n_number,".")) ? substr($n_number,0,strrpos($n_number,".")) : $n_number;
                 $iii = (strrpos($n_number,".")) ? substr($n_number,strrpos($n_number,".")+1,strlen($n_number)-1)+1 : $n_number+1;
                 $n_number = (strrpos($n_number,".")) ? substr($n_number,0,strrpos($n_number,".")).'.'.$iii : $iii;                 
             }
             if ($n > $n_last && $n_last != 0) {
                 $iii = 1;
                 $n_number = $n_number.'.'.$iii;
             }
             if ($n == $n_last) {
                 $iii++;
                 $n_number = (strrpos($n_number,".")) ? substr($n_number,0,strrpos($n_number,".")).'.'.$iii : $iii; 
             }
            $text = substr($line, $n,-$n);
            $toc[$i] =  '<li class="toc_'.$n.'">'.(($n <= 2) ? '<b>' : '').' <a href="'.implode('/', PRequest::get()->request).'#line'.$i.'"><span class="number">'.$n_number.'</span>'
                     . trim($text) . '</a>'.(($n <= 2) ? '</b>' : '').'</li>';

            $src[$i] = str_repeat("=", $n) . " [#line$i]" . $text . str_repeat("=", $n);
            $n_last = $n;
            $add = array('','');
         }
      }
   }
   $GLOBALS["ewiki_page_toc"] = &$toc;
    }
   $src = implode("\n", $src);
}


#-- injects toc above page
function ewiki_toc_view_prepend(&$html) {

    global $ewiki_page_toc;
    $words = new MOD_words($this->getSession());

    if (count($ewiki_page_toc) >= 3) {
       $html = '<table summary="Table of Contents" class="toc" id="toc">
                 <tbody><tr>
                  <td>'
          . "<div class=\"page-toc\" id=\"wiki-page-toc\">\n"
          . ( EWIKI_TOC_CAPTION ? '<div class="page-toc-caption">'.ewiki_t("toc")."</div>\n" : '')
          . '<ol>'. implode("", $ewiki_page_toc) . '</ol>'
          . "\n</div>\n"
          . "</td>
          </tr></tbody>
          </table>"
          . $html;
    }
    
   // $ewiki_page_toc = NULL;
}


?>
