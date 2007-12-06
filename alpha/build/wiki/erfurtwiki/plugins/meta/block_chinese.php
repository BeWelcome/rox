<?php
/*
   This plugin blocks any contribution that contains chinese characters
   (as HTML entities). This is not meant to be impolite or discriminate
   Chinese, but because there are a few very aggressive web/link spammers
   this is currently the best workaround for primarily English sites.
   
   IMPORTANT: this plugin must be loaded together with (before) the 
   'fragments/head/meta.php' code snippets.
*/

$ewiki_plugins["handler"][] = "ewiki_block_cjk_spam";
$ewiki_plugins["view_final"][] = "ewiki_warn_cjk_spam";


function ewiki_cjk_entities($html) {
   if (preg_match_all("/&#(x?[0-9a-f]+)/i", $html, $uu))
   foreach ($uu[1] as $char) {
      $char = strtolower($char);
      if ($char[0]=="x") {
         $char = hexdec(substr($char, 1));
      }
      $char = (int) $char;
      if ( ($char >= 0x3200) && ($char <= 0x9999)  // CJK A+Unified+..
        or ($char >= 0x2E80) && ($char <= 0x303F) )
      {
         return(true);
      }
   }
   return(false);
}


function ewiki_block_cjk_spam($id, &$data, $action) {
   global $ewiki_cjk;
   $ewiki_cjk = 0;
   if (ewiki_cjk_entities($data["content"])) {
      $ewiki_cjk = 1;
      $data["meta"]["meta"]["robots"] = "NOINDEX,NOFOLLOW,NOPAGERANK,NOCOUNT,NOARCHIVE";
   }
}

function ewiki_warn_cjk_spam(&$o, $id, &$data, $action) {
   global $ewiki_cjk;
   if ($ewiki_cjk) {
      $o = <<<END
<div class="system-message" style="background:#883333; color:#ffffff; border:2px solid #554444; padding:3px; margin:5px;">
  <big><b style="color:#ffffcc">&lt;META name="ROBOTS" content="<blink>NOINDEX</blink>,NOFOLLOW"&gt;</b></big>
  <br>
  <b>ATTENTION</b>: This page will <u>no longer be indexed by Google</u> and other
  search engines. This is because it contains Chinese characters, and we have
  been link-spammed too much in the last time. Apologies if you just wanted
  to check it out.
</div>\n
$o
END;
   }
}

?>