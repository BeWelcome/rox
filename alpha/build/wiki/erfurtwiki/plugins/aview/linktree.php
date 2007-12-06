<?php

#
# Generates a page tree from the currently viewed page up to
# the "root_page" and prints it below the EditThisPage-line.
# Usually this "root_page" is the same as the FrontPage of
# your Wiki (EWIKI_PAGE_INDEX), but this can be overriden with
# $ewiki_config["root_page"] or EWIKI_LINKTREE_DEST.
#
# Modified by AndyFundinger (http://erfurtwiki.sourceforge.net/?id=AndyFundinger)
#

define("EWIKI_LINKTREE_UL", 0);		// else a link::list will be printed

#-- register
$ewiki_plugins["view_append"][] = "ewiki_view_append_linktree";


#-- plugin func
function ewiki_view_append_linktree($id, $data, $action) {
   global $ewiki_config;

   $refs = ewiki_db::GETALL(array("refs"));
   $refs = ewiki_f_parent_refs($refs);

   #-- $dest
   if (empty($ewiki_config["root_page"])) {
      if (defined("EWIKI_LINKTREE_DEST")) {
         $ewiki_config["root_page"] = EWIKI_LINKTREE_DEST;
      }
      else {
         $ewiki_config["root_page"] = EWIKI_PAGE_INDEX;
      }
   }
   $dest = &$ewiki_config["root_page"];

   $depth = 0;
   $paths = array($id=>$id);
   $current = $id;   
/*
 *   $paths["Current"] = "Current";
 *   $paths["WorldWideWeb\nWikiWikiWeb\nErfurtWiki"] = "ErfurtWiki";
 */

   #-- retry until at least one $path is found
   while ( (!in_array($dest, $paths)) && ($depth <= 15) && (count($paths)<=100000)) {

      $depth++;

      #-- expand every last path entry
      foreach ($paths as $pathkey=>$uu) {

         #-- mk subkey from pathkey
         if ($p = strrpos($pathkey, "\n")) {
            $lkey = substr($pathkey, $p+1);
         }
         else {
            $lkey = $pathkey;
         }

         #-- append tree leafs
         if ($walk = $refs[$lkey]) {
            foreach ($walk as $add=>$uu) {
               $paths[$pathkey."\n".$add] = $add;
            }
            unset($refs[$lkey]);
         }
      }
   }

   #-- print results
   foreach ($paths as $key => $name) {

      $tree = array_reverse(explode("\n", $key));
      $GLOBALS["ewiki_page_sections"] = array();

      if (($name == $dest) && (count($tree) >= 2))  {

         $GLOBALS["ewiki_page_sections"][] = $tree[1];

         if (EWIKI_LINKTREE_UL) {
            $o .= ewiki_f_tree($tree, 0);
         } else {
            $o .= ewiki_f_tree2($tree, 0);
         }

      }
   }

   ($o) && ($o = "<div class=\"link-tree\">$o</div>\n");


   return($o);
}


#-- outputs the given pages in a treelist
function ewiki_f_tree(&$pages, $n=1) {

   if ($id = $pages[0]) {

      $o .= "<ul>";
      $o .= ($n ? "<li>" : "") .
            '<a href="'.ewiki_script("",$id).'">'.$id.'</a>' .
            ($n ? "</li>" : "") . "\n";
      $o .= ewiki_f_tree(array_slice($pages, 1));
      $o .= "</ul>\n";
   }

   return($o);
}


#-- outputs a flat link list
function ewiki_f_tree2(&$pages, $n=1) {

   foreach ($pages as $id) {
      $o[] = '<a href="'.ewiki_script("",$id).'">'.$id.'</a>';
   }

   // "::" instead of "&rarr;" may also look nice
   return(implode(" &rarr; ", $o) . "<br />");
}



#-- build parents array of (reverse) string $refs from the database
function ewiki_f_parent_refs($refs) {

   $pages = array();

   #-- decode refs
   while ($row = $refs->get()) {
      $parent = $row["id"];
      foreach (explode("\n", $row["refs"]) as $page) {

         if (strlen($page)) {
            $pages[$page][$parent]=1;
         }

         //echo("($page,$parent) ");
      }
      //echo("\n");
   }

   return($pages);
}


?>