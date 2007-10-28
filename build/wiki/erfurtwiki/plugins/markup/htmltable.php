<?php

/*
   You can use the ordinary html <table>, <tr> and <td> tags in all wiki
   pages, if you activate this plugin. Standard attributes are allowed
   (bgcolor, width, class, style, align, ...). It provides only limited 
   tag correction support, but you can often leave out </tr> and </td>:

   <table width="100%">
     <tr> <td> cell1
          <td> cell2
     <tr> <td> row2
          <td> cell4
   </table>
*/


$ewiki_plugins["format_block"]["htmltable"][] = "ewiki_markup_fblock_htmltable";
$ewiki_config["format_block"]["htmltable"] = array("&lt;table", "&lt;/table&gt;", false, 0x0027);


function ewiki_markup_fblock_htmltable(&$c, &$in, &$ooo, &$s) {

   if (($p = strpos($c, "&gt;")) !== false) {


      // clean <table> start and </table> end tag
      $c = "<table " . ewiki_markup_htmltable_attrs(substr($c, 0, $p))
         . ">" . substr($c, $p + 4) . "</table>";
      
      // clean <td> and <tr> tags
      $c = preg_replace('#&lt;(/?td|/?tr)(.*?)&gt;#e',
            '"<\\1" . ewiki_markup_htmltable_attrs("\\2") . ">"', $c);

      // insert missing </tr> and </td>
      $c = preg_replace('#(?<!</t[dr]>)\s*<(t[dr])#', '</\\1><\\1', $c);

      // add last closing </td> and </tr> tags
      $c = preg_replace('#(?<!</tr>)(\s*</table>)#', '</tr>\\1', $c);
      $c = preg_replace('#(?<!</td>)(\s*</tr>\s*</table>)#', '</td>\\1', $c);

      // remove redundant closing tags
      $c = preg_replace('#(<table[^>]+?>\s*)\s*(</t[dr]>)#', '\\1', $c);
      $c = preg_replace('#(</?tr>\s*)(\s*</t[dr]>)+#', '\\1', $c);
      $c = preg_replace('#(</td>\s*)(\s*</td>)*#', '\\1', $c);
   }
}


function ewiki_markup_htmltable_attrs($str) {
   if (preg_match_all('/(\s+(class|style|width|height|align|bgcolor|valign|border|colspan|rowspan|cellspacing|cellpadding)=(\w+|"[^"]+"))/', $str, $uu)) {
      $str = implode("", $uu[1]);
   }
   return($str);
}


?>