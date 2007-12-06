<?php

/*
   Current markup allows to use [Title | ForPageName] to create a link
   to a page, but use a different title. However other Wikis have the
   meaning swapped [PageName|title], and this often confuses people.

   Therefore this plugin provides an (unreliable) workaround, that checks
   for existing of a page which the name of either side of the dash |
   in square brackets, and then decides wich side contained the PageName
   and which the title.
*/



$ewiki_plugins["link_notfound"][] = "ewiki_linking_titlefix";


function ewiki_linking_titlefix(&$title, &$href, &$href2, &$type) {

   global $ewiki_links;

   $find = ewiki_db::FIND(array($title));
   if ($find[$title]) {

      $uu = $href;
      $href = $title;
      $title = $uu;

      $str = '<a href="' . ewiki_script("", $href) . htmlentities($href2)
           . '">' . $title . '</a>';
      $type = array("wikipage", "title-swapped");
      return($str);
   }

}


?>