<?php

# Lists all used words inside WikiPageNames and shows a list of them
# (similar to PageIndex) - but it redirects the words to PowerSearch,
# which also needs to be installed therefor!


define("EWIKI_PAGE_WORDINDEX", "WordIndex");
$ewiki_plugins["page"][EWIKI_PAGE_WORDINDEX] = "ewiki_page_wordindex";


function ewiki_page_wordindex($id, $data, $action) {

   global $ewiki_plugins;

   $o = ewiki_make_title($id, $id, 2);

   $src = "";

   $result = ewiki_db::GETALL(array("flags"));
   while ($row = $result->get()) {
        if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($row["id"], $uu, "view")) {
            continue;
        }   
        if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
            $src .= " " . $row["id"];
        }
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
   $o .= ewiki_list_pages($sorted, $limit=0, $vat=1, $pf_list_pages);

   return($o);

}


 ?>