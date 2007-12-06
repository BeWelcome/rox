<?php

/*
  This is another workaround plugin providing <code>...</code> and
  <php>...</php> escapes for the current rendering kernel.
*/


$ewiki_plugins["render"][0] = "ewiki_format_pre_code";


function ewiki_format_pre_code($wsrc, $sl=1, $hl=EWIKI_ALLOW_HTML, $sh=0) {

   $html = "";

   $loop = 20;
   while (preg_match("#^(.*?\n)?<(code>|php>|\?php|\?)(.+?)\n(</code|</php|\?)>#s", $wsrc, $uu) && ($loop--)) {

      $rend = &$uu[1];
      $code = &$uu[3];
      $wsrc = substr($wsrc, strlen($uu[0]));

      $html .= ewiki_format($rend,  $sl,$hl,$sh);
      $html .= "<pre>".
               ewiki_format_pre_code_escape($code, $uu[2]!="code>") .
               "\n</pre>";

   }

   if (strlen($wsrc)) {
      $html .= ewiki_format($wsrc,  $sl,$hl,$sh);
   }

   return($html);
}

function ewiki_format_pre_code_escape($html, $highl) {

   $html = trim($html, "\n");

   if ($highl) {
      ob_start();
      $html = highlight_string($html);
      $html = ob_get_contents();
      ob_end_clean();
   }

   return($html);
}

?>