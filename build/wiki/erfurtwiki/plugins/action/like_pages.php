<?php

/*
   LikePages like in WardsWiki
   another form of search function, useful for larger wikis
*/


 $ewiki_plugins["action"]["like"] = "ewiki_page_like";
 $ewiki_t["en"]["LIKE_TITLE"]= "Pages like ";

 function ewiki_page_like($id, $data, $action) {

    preg_match_all("/([".EWIKI_CHARS_U."][".EWIKI_CHARS_L."]+)/", $id, $words);

    $pages = array();
    foreach ($words[1] as $find) {

       $result = ewiki_db::SEARCH("id", $find);
       while ($row = $result->get()) {
            if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($row["id"], $row, "view")) {
                continue;
            }   

          $pages[$row["id"]] = "";
       }

    }

    $o = ewiki_make_title($id, ewiki_t(strtoupper($action)."_TITLE"), 3);
    $o .= ewiki_list_pages($pages, 0);

    return($o);
 }


?>