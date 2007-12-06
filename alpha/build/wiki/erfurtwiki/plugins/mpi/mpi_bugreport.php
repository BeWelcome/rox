<?php

/*
   Allows users to submit a bug report, automatically creates a new bug
   page and adds a link to the current page (BugReports). It is used in
   conjuntion with BugReportState on the ErfurtWiki: demo site.
*/


$ewiki_plugins["mpi"]["bugreport"] = "ewiki_mpi_bugreport";



// view <form>
function ewiki_mpi_bugreport($action, $args, &$iii, &$s) 
{
    global $ewiki_id;
    $MAIN = $ewiki_id;   //"BugReports";
    ($ELSE = $args[0]) or ($ELSE = rtrim($MAIN,"s"));
    $o = "";

    if ($_REQUEST["br_save"]) {

       #-- check parameters
       $content = $_REQUEST["content"];
       $title = $_REQUEST["title"];
       $author = $_REQUEST["author"];
       if (strlen($content) < 50) {
          return("<p><b>Insufficient information for a useful BugReport.</b></p>");
       }
       if (strstr($title,"???")) {
          $title = substr($content, 0, 50);
       }
       
       #-- make page name for bug report
       $new_id = ewiki_title_to_pagename($title, $ELSE);
       

       #-- generate bug page text
       $new = "This page is part of the $MAIN series. Please go there to submit a new bug or to see the list of all existing reports.\n";
       if ($m=$_REQUEST["br_notify"]) {
          $new .= "\n[notify:$m]\n";
       }
       $new .= "\n----\n\n"
            . "!! $title\n\n";
       foreach ($_REQUEST["i"] as $i=>$v) {
          if ($v != "unknown") {
             $new .= "| $i | $v |\n";
          }
       }
       $new .= "| status | __open__ |\n <?plugin BugReportState ?"."> \n";
       $new .= "\n$author: $content\n\n";
       
       #-- create new bug report page
       $data = ewiki_db::CREATE($new_id);
       $data["content"] = $new;
       ewiki_db::UPDATE($data);
       $data["version"] = 1;
       $ok = ewiki_db::WRITE($data);
       if (!$ok) {
          return("<b>Error</b> while creating new bug report page. Please go back and change the title (at least two words required), so we can try again.");
       }

       #-- store bugreport entry on main page
       $data = ewiki_db::GET($MAIN);
       $list_e = "| __open__ | $new_id | $title |\n";
       $data["content"] = preg_replace("/(\n\|.+\|.+\|\s*\n)/", "$1$list_e", $data["content"], 1);
       ewiki_db::UPDATE($data);
       $data["version"]++;
       ewiki_db::WRITE($data);
       if ($notify=function_exists($pf="ewiki_notify_edit_hook")) {
          $pf($MAIN, $data, $uu);
       }

       #-- append to page output
       $iii[] = array(
          "\n\n| new bug report [#added \"added\"] as $new_id |\n\n",
          0xFFFF,
          "core"
       );

    }
    else {

       $url = ewiki_script("", $ewiki_id);
       $ver = EWIKI_VERSION;
       $current_vers = "<option>$ver";
       for ($i=$ver[strlen($ver)-1]-1; $i>0; $i--) {
          $current_vers .= "<option>" . substr($ver, 0, strlen($ver)-1) . $i;
       }
       $o .=<<<EOT
<form style="border:2px #444455 solid; background:#9999aa; padding:5px;"class="BugReport" action="$url#added" method="POST" enctype="multipart/form-data">
bug title: <input type="text" name="title" value="???" size="50">
<br/>
<br/>
YourName: <input type="text" name="author" value="anonymous" size="30">
<br/>
<br/>
your ewiki version: <select name="i[ewiki version]"><option>unknown<option>other{$current_vers}<option>CVS today<option>CVS yesterday<option>CVS last week<option>two weeks old<option>latest -dev<option>R1.02a<option>R1.01f<option>R1.01e<option>R1.01d<option>R1.01c<option>R1.01b<option>R1.01a</select>
plattform: <select name="i[operating system]"><option>unknown<option>other<option>Win4<option>NT (2K,XP)<option>Unix<option>Linux<option>OS X</select>
<br/>
database: <select name="i[database backend]"><option>unknown<option>mysql<option>db_flat_files<option>db_fast_files<option>anydb: other<option>anydb: pgsql<option>dzf2<option>dba/dbm<option>phpwiki13<option>zip<option>other<option>own</select>
php version: <select name="i[php version]"><option>unknown<option>4.0.x<option>4.1.x<option>4.2.x<option>4.3.x<option>4.4/4.5 (CVS)<option>5.0-beta/rc<option>5.0.x<option>CGI/suPHP<option>PHP/FastCGI<option>SafeMode 4.x</select>
<br>
problem category (vague): <select name="i[bug category]"><option>unknown<option>general<option>feature request<option>get running<option>markup<option>links<option>error message<option>plugin<option>page plugin<option>action plugin<option>auth plugin<option>aview plugin<option>edit plugin<option>feature extension<option>link tweak plugin<option>mpi plugin<option>spages<option>lib extension<option>module plugin<option>db plugin<option>configuration<option>documentation</select>
<br/>
<br/>
long error description:<br /> 
<textarea name="content" cols="75" rows="8">
</textarea>
<br/><br/>
<input type="submit" name="br_save" value="create bug page">
<br/>
<br/>
email notification <input type="text" name="br_notify" value="" size="16">
<br/><small>(opt., only enter if you want get notified on any responses)</small>
<br/>
</form>
EOT;
    }

    return($o);
}



function ewiki_title_to_pagename($title, $else="BugReport") {

    global $ewiki_config;

    #-- try to make a wikilink out of the title
//    $title = strtolower($title);
    $title = preg_replace("/\b((?!not)[-_\w\d]{1,3})\b/", " ", $title);
    $title = ucwords($title);
    $link = preg_replace("/\s+/", "", $title);
    
    #-- if we don't get a WikiWord
    if (!$link || ewiki_db::GET($link)
    || !preg_match("/^([".EWIKI_CHARS_U."]+[".EWIKI_CHARS_L."]+){2,}/", $link)) {
       $n = 20;
       do {
          $link = "$else" . ($n++);
       }
       while (ewiki_db::GET($link));
    }

    return($link);    
}


?>