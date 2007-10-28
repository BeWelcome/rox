<?php

/*
   Allows people to "log in", so the personal WikiUserName gets saved
   when an edited page gets saved (a bit like 'BogoLogin' in PhpWiki).
   (no passwords, no real authentication, no _protected_mode)
*/


 $ewiki_plugins["page"]["WikiUserLogin"] = "ewiki_page_wikiuserlogin";


 if ($uu = $_REQUEST["wikiauthor"]) {
    $ewiki_author = $uu;
 }


 function ewiki_page_wikiuserlogin($id, $data, $action) {

    $o .= "<h2>$id</h2>\n";

    if (empty($_GET["wikiauthor"])) {
       $o .= '
       <form action="'.ewiki_script("login",$id).'" method="get">
          your WikiName <input type="text" name="wikiauthor" size="20">
          <br /><br />
          <input type="submit" value="log in">
          <input type="hidden" name="page" value="'.$id.'">
          <br /><br />
          <input type="checkbox" name="forever" value="1"> make cookie persistent forever
       </form>
       ';
    }
    else {
       $o .= '
       Your author name is now set to "'.$_GET["wikiauthor"].'". Please go ahead and
       start editing pages.
       ';
       if ($_REQUEST["forever"]) {
          setcookie("wikiauthor", $_GET["wikiauthor"]);
       }
       else {
          setcookie("wikiauthor", $_GET["wikiauthor"], time()*1.2);
       }
    }

    return($o);
 }


?>