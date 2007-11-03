<?php
/*
   This plugin adds the <tex>...</tex> tags which allow you to
   integrate formulas into wiki pages, if you have the MimeTeX
   package (from John Forkosh) installed. You can get it from
   [http://www.forkosh.com/mimetex.html] (source or as binary)
   
   The original idea and implementation of this plugin was done by
   Francois Vanderseypen <illumineo*users·sf·net> as you can see at
   [http://netron.sourceforge.net/ewiki/netron.php?id=MimeTeX]
   
   <tex> \aleph = \bigsum_{\alpha,\beta}\Bigint_{0}^{\infty}\:\Gamma_{\alpha\beta}(x)\,dx </tex> 
*/

define("MIMETEX_BIN", "mimetex");
   # the actual mimetex utility (on poorly configured UNIX boxes you would
   # have to give the full path name here)
   
define("MIMETEX_DIR", "/home/www/user28494/htdocs/ewiki/var/mimetex/");
   # where generated images are thrown in (world-writeable!), you could
   # use "/tmp" if _INLINE was ok for your users
   
define("MIMETEX_PATH", "/ewiki/var/mimetex/");
   # where to access the generated images then (prefix for the <img> URLs)
   
define("MIMETEX_INLINE", 0);
   # if you'd instead like data: URIs for images (does not work with IE <7)


$ewiki_plugins["format_block"]["tex"]= array("mimetex_format_block");
$ewiki_config["format_block"]["tex"] = array("&lt;tex&gt;", "&lt;/tex&gt;", false, 0x0410);


function mimetex_format_block(&$str, &$in, &$iii, &$s, $btype) {
   $str = mimetex_generate($str);
}


/*
   calls mimetex to create image or returns link to cached file
*/
function mimetex_generate($formula) {

   $formula = preg_replace("/[\s]+/", "", $formula);
   $filename = md5($formula).".gif";
   $fullname = MIMETEX_DIR."/$filename";
   
   $url = false;
   if (is_file($fullname)) {
      $url = MIMETEX_PATH."/$filename";
   }
   else {
      $cmd = MIMETEX_BIN . " -e $fullname '" . escapeshellarg($formula) . "'";
      system($cmd, $status);
      if (!$status_code) {
         $url = MIMETEX_PATH."/$filename";
      }
   }

   if ($url) {
      if (MIMETEX_INLINE) {
         $url = "data:image/gif;base64," . base64_encode(implode("", file($fullname)));
      }
      return('<img src="'.$url.'" alt="'.htmlentities($formula).'" align="absmiddle" />');
   }
   else {
      return("[MimeTex could not convert formula \"$formula\".]");
   }
}


?>