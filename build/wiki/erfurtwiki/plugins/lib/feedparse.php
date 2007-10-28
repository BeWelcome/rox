<?php
/*
   Deciphers RSS and Atom feeds into a simple array/hash structure. Does not
   evaluate MIME parameters, but elsewhise is rather compliant, even if it
   only uses simplified and data-oriented XML extraction.
   It often can evaluate RDF/"RSS"-1.0 feeds, if they aren't too RDFish
   (no real parser for that), adn the text-only RSS3.0 is also supported.

   @depends: http, http_cached, xml
*/


#-- fetches, caches and decodes RSS/Atom feeds
function ewiki_feed_get($url) {
   global $ewiki_cache_ctype;
   if ($xml = ewiki_cache_url($url)) {
      return ewiki_feed_parse($xml);
   }
}


#-- decodes RSS/Atom feeds into struct/hash
function ewiki_feed_parse($xml, $ctype="text/xml") {
   $xml = trim($xml);
   $r = array();
   #-- guess charset
   if (preg_match("/charset=[\"']?([-\w\d]+)/i", $ctype, $uu)) {
      $charset = $uu[1];
   }
   else {
      $charset = "ISO-8859-1";   // default for most stuff over HTTP
   }
   #-- xml
   if ($xml[0] == "<") {
      $xml = new easy_xml_rss($xml, $charset);
      $xml->parse();
      if (isset($xml->channel)) {
         $r = array($xml->channel, $xml->item);
      }         
   }
   #-- text/rfc822 rss
   else {
      $item = ewiki_decode_rfc822($xml);
      $channel = $item[0];
      unset($item[0]);
      $r = array($channel, $item);
   }
   #-- unified timestamps
   if ($r[1]) foreach($r[1] as $i=>$d) {
      $r[1][$i]["time"] = ewiki_decode_datetime($d["pubDate"]);
   }
   return($r);
}


#-- separates multiple blocks of name:value pairs
function ewiki_decode_rfc822($text) {
   $blocks = array();
   foreach (preg_split("/\r?\n\r?\n/", $xml) as $part) {
      $r = array();
      foreach (preg_split("/\r?\n(?>[^\s])/") as $field)
      {
         $r[trim(strtolower(strtok($field, ":")))]
            = trim(preg_replace("/\s+/", " ", strtok("\000")));
      }
      $blocks[] = $r;
   }
   return($blocks);
}


#-- tries to decipher a date+time string into a unixish timestamp
function ewiki_decode_datetime($str, $localize=1, $gmt=0) {
   $done = 0;
   $months = array_flip(array("Err", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"));
   
   #-- 8601
   if (($str[4] == "-")
   and preg_match("/(\d+)-(\d+)-(\d+)(T(\d+):(\d+)(:(\d+))?)?/", $str, $uu)) {
      $t = gmmktime($uu[5], $uu[6], $uu[8], $uu[2], $uu[3], $uu[1]);
   }
   #-- mbox, ANSI C asctime()
   elseif (($str[3] == " ")
   and preg_match("/\w+ (\w+) +(\d+) (\d+):(\d+):(\d+) (\d+)/", $str, $uu)) {
      $t = gmmktime($uu[3], $uu[4], $uu[5], $months[$uu[1]], $uu[2], $uu[6]);
      $gmt = 1;
   }
   #-- rfc822/1123, (rfc850/1036), mostly for HTTP
   elseif (1
   and preg_match("/\w+, (\d+)[- ](\w+)[- ](\d+) (\d+):(\d+):(\d+)/", $str, $uu)) {
      $t = gmmktime($uu[4], $uu[5], $uu[6], $months[$uu[2]], $uu[1], $uu[3]);
      $gmt = 1;
   }
   #-- already was a timestamp
   elseif (((int)$str) == $str) {
      $t = (int)$str;
      $gmt = 1;
   }
   #-- last resort
   else {
      $t = strtotime($str);
      $gmt = 1;
   }

   #-- is local time (iso8601 only)
   if (!$gmt && $localize && preg_match('/([+-])(\d+):(\d+)$/', $str, $uu)) {
      $dt = $uu[1] * 60 + $uu[2];
      if ($uu[1] == "+") {
         $t -= $dt;
      }
      else {
         $t += $dt;
      }
   }
   
   return($t);
}


?>