<?php
/*
   The error handler provided here will feed all errors and warnings
   into HTTP headers of the form "X-Error-NNNNN: ...", so they can't
   disturb page output or make XML output invalid. This allows to
   turn on complete error_reporting() without any functionality loss
   due to premature output.
   You of course need a good Web browser that can easily display all
   response headers then for developing - 'w3m' makes an excellent
   choice! (press "=" there)
*/

set_error_handler("ewiki_http_header_errors");
ini_set("html_errors", 0);

function ewiki_http_header_errors($errno, $msg, $file, $line, $lvars) {

   static $error_types = array(
      E_PARSE => "PARSE ERROR",
      E_ERROR => "ERROR",
      E_WARNING => "WARNING",
      E_NOTICE => "NOTICE",
      E_STRICT => "STRICT",
      E_USER_ERROR => "USER ERROR",
      E_USER_WARNING => "USER WARNING",
      E_USER_NOTICE => "USER NOTICE",
   );
   ($errtype = $error_types[$errno]) or ($errtype = "UNDEF ERROR");
   
   #-- check for @ and disabled errors
   $emask = get_cfg_var("error_reporting");
   if (! ($emask & $errno)) {
      return;
   }

   #-- output
   $msg = strtr($msg, "\r\n\t\f", "    ");
   $msg = "$errtype: $msg in $file, line #$line";
   if (headers_sent()) {
      print "\n<!--<div class=\"php-error\">$msg</div>-->\n";
   }
   else {
      $no = crc32($msg);
      $no = ($no & 0xFFFF) ^ ($no >> 16);
      header("X-Error-$no: $msg");
      if ($errno == E_FATAL) { header("Status: 500 Something bad happened"); }
   }
}

?>