<?php
/*
   This script embeds meta data (from meta edit box) into generated .html
   pages:
   - <meta name="keywords" ...
   - <link rel="prev" ...

   include() this fragment AFTER you have loaded 'ewiki.php' and have
   run ewiki_page() at least once:

    <&php
       include("ewiki.php");
       $CONTENT = ewiki_page();
    &><html>
    <head>
      <title><&php echo $ewiki_title; &></title>
      <&php include("fragments/head/meta.php"); &>
      <&php include("fragments/css.php"); &>
    </head>
    ...
*/

if ($ewiki_data && function_exists("ewiki_script")) {
   if ($m = &$ewiki_data["meta"]["meta"]) {

      #-- real page <meta> data
      $real_meta = array("keywords", "description", "author", "robots");
      foreach ($real_meta as $i) {
         if ($m[$i]) {
            echo '<meta name="'.$i.'" content="'.htmlentities($m[$i]).'">'."\n";
         }
      }

      #-- site structure meta <link>s
      $rel_links = array("prev", "next", "contents", "chapter", "section", "start");
      foreach ($rel_links as $i) {
         if ($m[$i]) {
            echo '<link rel="'.$i.'" href="'.ewiki_script("", $m[$i]).'">'."\n";
         }
      }

      #-- alternate representations
      $alt_repr = array("links", "edit");
      foreach ($alt_repr as $i) {
         if ($ewiki_action==$i) { $i = ""; }
         echo '<link rel="alternate" href="'.ewiki_script($i, $ewiki_id).'" title="'.htmlentities($i?$i:$ewiki_id).'">'."\n";
      }
   }
}

?>