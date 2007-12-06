<?php

/*
   Lists all used words inside WikiPageNames and shows a list of them
   (similar to PageIndex) - but it redirects the words to PowerSearch,
   which also needs to be installed therefore!
*/

 global $ewiki_plugins;
 echo ewiki_make_title($id, $id, 2);

 $result = ewiki_db::GETALL(array("flags"));
 $src = "";
 while ($row = $result->get(0, 0x0037, EWIKI_DB_F_TEXT)) {
    $src .= " " . $row["id"];
 }

 $src = ewiki_split_title($src, "SPLIT", 0);
 $chars = strtr(EWIKI_CHARS_U.EWIKI_CHARS_L, "_", " ");
 $src = preg_replace("/[^$chars]/", " ", $src);
 $src = explode(" ", $src);
 $src = array_unique($src);   //@ADD: word counting
 unset($src[""]);

 natcasesort($src);

 $sorted = array();
 foreach ($src as $i => $word) {

    if (strlen($word) >= 2) {

       $sorted[] = array(
          EWIKI_PAGE_POWERSEARCH,
          array("where"=>"id", "q"=>$word),
          $word, ""          //@ADD: display word count
       );

    }
 }
 unset($src);

 $pf_list_pages = $ewiki_plugins["list_dict"][0];
 echo ewiki_list_pages($sorted, $limit=0, $vat=1, $pf_list_pages);


?>