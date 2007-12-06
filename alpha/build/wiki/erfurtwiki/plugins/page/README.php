<?php

/*
  This is a plugin for the sf.net site, which splits the README
  into multiple parts, that then will be printed read-only.

  It was not developed to understand the format used by other text
  files, so please do not annoy me, if it refuses to work for you.
  Examples are examples. No support.  -- milky
*/


$ewiki_plugins["page"]["README"] = "ewiki_page_README";
$ewiki_plugins["page"]["README.config"] = "ewiki_page_README";
$ewiki_plugins["page"]["README.plugins"] = "ewiki_page_README";
$ewiki_plugins["page"]["README.fragments"] = "ewiki_page_README";
$ewiki_plugins["page"]["ProtectedMode"] = "ewiki_page_README";
$ewiki_plugins["page"]["INTERNALS"] = "ewiki_page_README";
#$ewiki_plugins["page"]["README.de"] = "ewiki_page_README";


function ewiki_page_README($id, $data, $action) {

   $UNDERLINE = "¯¯¯";
   $SECTIONSEP = "--------------------------------------------------------------";

   #-- open "README" text file
   if (($r = fopen("$id", "r")) or ($r = fopen("doc/$id", "r"))) {

      #-- received request for a specific entry
      if (($para = $_REQUEST["paragraph"]) || ($goto=$_REQUEST["goto"])) {

         $str_next = $str_prev = "???";

         #-- exact "title" pattern given
         $n = 0;
         if ($para) {
            while (!feof($r) && (trim($line) != $para)  )
            {
               $line = fgets($r, 4096);
               if (strstr($line, $UNDERLINE)) { 
                  $n++;
                  $str_prev = $last;
               }
               $last = $line;
            }
            $n++;
         }
         #-- search for a paragraph number
         elseif ($goto) {
            while (!feof($r) && ($n < $goto) )
            {
               $para = $line;
               $line = fgets($r, 4096);
               if (strstr($line, $UNDERLINE)) { 
                  $n++;
                  $str_prev = $last;
               }
               $last = $line;
            }
         }

         if ( ($goto&&($n==$goto)) || strstr(fgets($r, 4096), $UNDERLINE)) {
            $hl = str_replace("[", "![", "\n!! $para\n\n<pre>");
            $line = "";
            $lines = array();
            $strip_n = -1;
            while (!feof($r) && (!strstr($line, $UNDERLINE))) {
               $last = $line;
               $line = fgets($r, 4096);
               if ($strip_n == -1) {
                  preg_match("/^([ ]+)/", $line, $uu);
                  $strip_n = strlen($uu[1]);
               }
               if ($strip_n) {
                  $line = preg_replace("/^[ ]{{$strip_n}}/", "", $line);
               }
               $lines[] = $line;
            }
            $str_next = $last;

            array_pop($lines);
            array_pop($lines);
            $src = rtrim(implode("", $lines)); $lines = "";

            #-- transformation
#            $src =  str_replace("[", "![", $src);
            if (preg_match_all('/^\s+(EWIKI_[^\s]+)/m', $src, $uu)
                && (count($uu[1])>10)   ||
                preg_match_all('/^[ ]+([^\n]+)\n\s\s\s------+/m', $src, $uu)
                && (count($uu[1])>7)
              )
            {

               $src = preg_replace('/^[ ]+(EWIKI_[^\s]+)/m',
                                   ' $1#[$1":"]', $src);
               $src = preg_replace('/^[ ]+([^\n]+)\n\s\s\s(------+)/m',
                                   "\n   $1#[$1\"\"]\n   $2", $src);
               foreach ($uu[1] as $anchor) {
                  $pre .= "- [#$anchor \"$anchor\"]\n";
               }
               $src = $pre."\n".$src;
            }

            $end = "\n</pre>\n";
            $o .= ewiki_format($hl.$src.$end);

         }

      }

      #-- otherwise print all paragraph headlines in ordered lists
      else {

         $o .= "\n<h2>$id overview</h2>\n";

         $ol_level = 0;
         $first = 0;
         $last = "";

         $o .= "<ol>\n";

         while (!feof($r)) {

            $line = fgets($r, 4096);

            if (strstr($line, $SECTIONSEP)) {
               while($ol_level) {
                  $o .= "</ol>\n";
                  $ol_level--;
               }
               $first=1;
            }

            if (strstr($line, $UNDERLINE) && ($lastline!=="$id")) {

               $new_ol_level = 1 - $first + (strpos($line, $UNDERLINE)>2 ?1:0);
               while($ol_level > $new_ol_level) {
                  $o .= "</ol>\n";
                  $ol_level--;
               }
               while($ol_level < $new_ol_level) {
                  $o .= "<ol>\n";
                  $ol_level++;
               }
               $first = 0;

               $o .= '<li><a href="' . ewiki_script("", "$id",
                     array("paragraph"=>$lastline)) . '">' . $lastline .
                     '</a><br /></li>' . "\n";

               $section=1;
            }

            $lastline = trim($line);
         }

         while($ol_level--) {
            $o .= "</ol>\n";
         }

         $o .= "</ol>\n";

      }

   }

   if ($n) {
      $o .= '<a href="' . ewiki_script("", "$id", "goto=".($n-1)).'">'.
            'prev &lt;&lt; "'.trim($str_prev).'"</a><br />';
      $o .= '<a href="' . ewiki_script("", "$id", "goto=".($n+1)).'">'.
            'next &gt;&gt; "'.trim($str_next).'"</a><br />';
   }

   $br = ewiki_script("", "BugReports");
   $us = ewiki_script("", "UserSuggestions");
   $dc = ewiki_script("", "{$id}.Discussion");
   $o .= "
<br />
<hr>
You cannot modify the {$id} file, but anyhow any ideas or suggestion should
as usually get filed on <a href=\"$br\">BugReports</a>,
<a href=\"$us\">UserSuggestions</a> or even better the
<a href=\"$dc\">{$id}.Discussion</a>.
<br />
"; 

   return($o);
}

?>