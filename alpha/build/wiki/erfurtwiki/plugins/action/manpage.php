<?php
/*
   Returns current page as man(1) document (nroff/troff). This script
   includes a separate formatting kernel specifically for that case,
   which of course strips out many things (CSS and markup extensions).
   It is regex based and therefore not all too fast.
*/


$ewiki_plugins["action"]["man"] = "ewiki_action_manpage";
$ewiki_config["action_links"]["view"]["man"] = "man(1)";


#-- return page in man(1) format to client
function ewiki_action_manpage($id, &$data, $action) {

   // aaargh! shouldn't that be in text/ ?!
   header("Content-Type: application/x-troff-man");
   $fn = urlencode($id) . ".1";
   header("Content-Disposition: inline; filename=\"$fn\"");
   
   #-- output
   die(ewiki_format_nroff($data["content"]));
}


function ewiki_format_nroff(&$source)
{
   global $ewiki_id, $ewiki_config, $mancat;

   $mancat = "1";   // alternatively use "w" or "wiki"
   $o = "";
   $source = trim($source);
   if (substr($source, 0, 2) == "#!") {
      $source = substr($source, strpos($source, "\n") + 1);
   }
   
   #-- wiki2man line by line
   foreach (explode("\n", $source) as $line) {

      #-- escaping
      $line = preg_replace("/^[.]/", "\n .", $line);
      
      #-- headlines
      $line = preg_replace("/^[!]+/", "\n.SH ", $line);

      #-- definiton lists
      $line = preg_replace("/^:(.+?):/", "\n.TP 5\n.B $1\n", $line);

      #-- indented text
      $line = preg_replace("/^( [ ]+)/e", '"\n.TP ".strlen("$1")."\n"', $line);
//      $line = ltrim($line, " \t");

      #-- ordinary lists
      $line = preg_replace("/^\*/", "\n\n*", $line);
      $line = preg_replace("/^#/", "\n\n#", $line);

      #-- text style
      $line = preg_replace("/__(.+?)__/", "\n.B $1\n", $line);
      $line = preg_replace("/\*\*(.+?)\*\*/", "\n.B $1\n", $line);
      $line = preg_replace("/'''(.+?)'''/", "\n.B $1\n", $line);
      $line = preg_replace("/''(.+?)''/", "\n.I $1\n", $line);
      $line = preg_replace("/'''''(.+?)'''''/", "\n.BI $1\n", $line);

      #-- rip out some things
      $line = preg_replace("/@@[^\s]+/", "", $line);
      
      #-- paragraphs
      if (!strlen($line)) {
         $o .= "\n\n";
      } 

      #-- ok, out
      $o .= addslashes($line) . " ";
   }
   
   #-- highlight links
   ewiki_scan_wikiwords($source, $GLOBALS["ewiki_man_links"]);
   $o = preg_replace_callback($ewiki_config["wiki_link_regex"], "ewiki_format_man_linkrxcb", $o);
   
   #-- post fixes
   $o = preg_replace("/\n\s*\n+/", "\n\n", $o);
   $o = preg_replace("/^ ([^\s])/m", "$1", $o);

   #-- prefix output   
   $monthyear = strftime("%B %Y", time());
   $name = EWIKI_NAME;
   $o = ".\\\" this man page was converted by ewiki,\n"
      . ".\\\" many apologies if it doesn't work\n"
      . ".\\\"\n"
      . ".TH \"$ewiki_id\" $mancat \"$monthyear\" \"$name\" \"$name\"\n"
      . (!preg_match("/^\s*\.SH/", $o) ? "\n.SH $ewiki_id\n" : "")
      . "\n"
      . $o;
   
   return($o);
}


function ewiki_format_man_linkrxcb($uu) {
   global $ewiki_man_links, $mancat;

   $str = $uu[0];
   if (($str[0]=="!") or ($str[0] == "~") or ($str[0] == "\\")) {
      return substr($str, 1);
   }
   if ($str[0]=="[") {
      $str = substr($str, 1, strlen($str)-2);
   }

   if (strpos($str, '"')
   and (preg_match('/^\s*"(1:.+?)"\s*(2:.+?)\s*$/', $str, $uu)
   or preg_match('/^\s*(2:.+?)\s*"(1:.+?)"\s*$/', $str, $uu))) {
      list($uu2, $title, $href) = $uu;
   }
   elseif (strpos($str, '"')) {
      list($title, $href) = explode("|", $str, 2);
   }
   else {
      $href=$title=$str;
      if (strpos($href, "://") && strpos($href, " ")) {
         $href = strtok($str, " ");
         $title = strtok("]");
      }
      elseif (!$ewiki_man_links[$href]) {
         $href = "?";
      }
      else {
         $href = "$mancat";
      }
      if (strpos($title, "://")) {
         $href = 0;
      }
      elseif (strpos($title, ":")) {
         $href = ewiki_interwiki($href, $uu, $uu);
      }
   }
   
   return "\n.BI \"" . addslashes($title) . "\""
       . ($href ? ($href=="?" ? "?" : " \"(" . addslashes($href) . ")\"") : "")
       . "\n";
}

?>