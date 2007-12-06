<?php

/*
   This plugin provides a SearchAndReplace dialog page, which allows
   to grep for pages and to replace content/text using regular expression
   matching and replacing (as well as ordinary string replacing). It is
   very powerful, even if the interface and description make it appear
   difficult to use.

   Only moderators and superusers can use it ($ewiki_ring level must not
   be lower than 1).

   WARNING: security leak! moderates may be able to use the /e modifier
   to execute arbitrary code -- There is now a check for this, which logs
   such attempts.
*/


#-- plugin glue
$ewiki_plugins["page"]["SearchAndReplace"] = "ewiki_page_searchandreplace";


#-- impl
function ewiki_page_searchandreplace($id, $data, $action) {

   global $ewiki_ring, $ewiki_plugins;

   $o = ewiki_make_title($id, $id, 2);

   #-- admin requ. ---------------------------------------------------------
   if (!ewiki_auth($id,$data,$action, $ring=1, "_FORCE_LOGIN=1") || !isset($ewiki_ring) || ($ewiki_ring > 1)) {
      if (is_array($data)) {
         $data = "You'll need moderator/administrator privileges to use this.";
      }
      return($o .= $data);
   }

   #-- form ----------------------------------------------------------------
   if (empty($_REQUEST["snr_go"]) && empty($_REQUEST["snr_test"])) {
      $url = ewiki_script("", $id);
      $o .= ewiki_t(<<<END
Use this form to replace all occourences of a phrase in all WikiPages.
<br /><br />
<form action="$url" method="POST" enctype="multipart/form-data">
search for string<br />
<input type="text" name="snr_search_string" value="" size="30"><br />
<small>this text snippet always matches case-insensitive, used as
<b>first-stage</b> search string; leave it empty to use only the regular
expression matching (slower)</small><br />
look this string up only in <select name="snr_search_string_where"><option selected="selected" value="content">page content / body</option> <option value="id">page name / title</option></select><br />
<br />
<i>and/or</i> with <tt>/Perl/i</tt> regular expression<br />
<input type="text" name="snr_search_regex" value="" size="30"><br />
<small>this is <b>optional</b>, and is anyhow only used as second-stage search
pattern; if used allows to use regex backreferences in the replacement
string field</small><br />
<br />
then replace with string<br />
<input type="text" name="snr_replace" value="" size="30"><br />
<small>can contain backreferences \1 and \$1 if the regex search field was
used</small><br />
<br />
<input type="submit" name="snr_test" value="dry run / test regex"> &nbsp;
<input type="submit" name="snr_go" value="Replace All">
</form>
<br />
<br />
The regular expression matching is optional, you'll often only need the
simple string search field and another simple string in the replacement
field.
<br />
<br />
Please note, that this form allows to initially search for a simple string,
but you can leave this empty and only use a regex search. And as it is a
two stage searching, both patterns can be completely different.
<br />
<br />
Text replacement always happens in the WikiPages body, even if the simple
search string can be used to search for page names - if you do so, you
certainly need a second regular expression pattern for content replacement.
<br />
END
	);
   }
   else {
      $do = $_REQUEST["snr_go"];

      #-- prepare vars
      $search_where = $_REQUEST["snr_search_string_where"];
      $search_string = $_REQUEST["snr_search_string"];
      $search_regex = $_REQUEST["snr_search_regex"];
      $replacement = $_REQUEST["snr_replace"];
      if ($search_string == "*") {
         $search_string = "";
      }
      $search_string2 = preg_quote($search_string, "/");
      $replacement2 = addcslashes($replacement, "\$");

      #-- security check in search_regex      
      if (preg_match('/([\w\s]+)$/', $search_regex, $uu) && strstr($uu[0],"e")) {
         ewiki_log("use of regex '$search_regex' could be security circumvention attempt", 1);
         return($o . "wrong regex delimiter");
      }

      #-- complain
      if (empty($search_string) && empty($search_regex) || empty($replacement)) {
         return($o . "too few parameters, needs at least one search and a replacement string");
      }

      #-- initial database string search
      if (empty($search_string)) {
         $result = ewiki_db::GETALL(array("id", "version", "flags"));
      }
      else {
         $result = ewiki_db::SEARCH($search_where,$search_string);
      }

      #-- walk through pages
      while ($row=$result->get()) {

         #-- skip binary entries
         if (EWIKI_DB_F_TEXT != ($row["flags"] & EWIKI_DB_F_TYPE)) {
            continue;
         }
         $id = $row["id"];
         $save = false;
         $row = ewiki_db::GET($id);

         /*
            if (!ewiki_auth($id, $row, "edit", ...
            ...
         */

         if ($search_regex) {
            if (preg_match($search_regex, $row[$search_where], $uu)) {
               $save = true;
               $row["content"] = preg_replace($search_regex, $replacement, $row["content"]);
            }
         }
         elseif ($search_string) {
            if (stristr($row[$search_where], $search_string)) {
               $save = true;
               $row["content"] = preg_replace("/$search_string2/i", $replacement, $row["content"]);
            }
         }

         if ($save) {
            $o .= "· <a href=\"" . ewiki_script("", $id) . "\">" . htmlentities($id)
               . "</a> matched given search pattern<br />\n";
            if ($do) {
               $row["lastmodified"] = time();
               $row["author"] = ewiki_author("SearchAndReplace");
               $row["version"]++;
               if (ewiki_db::WRITE($row)) {
                  $o .= "&nbsp; changed.<br />\n";
               }
               else {
                  $o .= "&nbsp; database store error<br />\n";
                  $o .= "&nbsp; " . mysql_error() . "<br />\n";
               }
            }
         }
         
      }#-- while $result

      if ($do) {
         ewiki_log("SearchAndReplace for '$search_strinmg' and '$search_regex' to replace with '$replacement'");
      }
   }

   return($o);
}


?>