<?php

/*
   prints out a short summary of changed wiki pages
   (an "updated-articles-list")

   It respects following $ewiki_config[] entries:
    ["wikinews_num"] - how many new articles to be shown
    ["wikinews_len"] - string length of the excerpts
    ["wikinews_regex"] - use only pages that match this /pregex/
*/


$ewiki_plugins["page"]["WikiNews"] = "ewiki_page_wikinews";

function ewiki_page_wikinews($newsid, $data, $action) {
  global $ewiki_config;

   #-- conf
   ($n_num = $ewiki_config["wikinews_num"]) || ($n_num = 10);
   ($n_len = $ewiki_config["wikinews_len"]) || ($n_len = 512);
   ($c_regex = $ewiki_config["wikinews_regex"]) || ($c_regex = false);

  return(ewiki_make_title($newsid,$newsid, 2).ewiki_wikinews_summary($n_num,$n_len,$c_regex));
}

function ewiki_wikinews_summary($n_num,$n_len,$c_regex){
  global $ewiki_plugins, $ewiki_config;

  #-- fetch all page entries from DB, for sorting on creation time
  $result = ewiki_db::GETALL(array("lastmodified"));
  $sorted = array();
  while ($row = $result->get()) {
  
    if (EWIKI_DB_F_TEXT == ($row["flags"] & EWIKI_DB_F_TYPE)) {
  
      if ($c_regex && !preg_match($c_regex, $row["id"])) {
        continue;
      }
      
      $sorted[$row["id"]] = $row["lastmodified"];
    }
  }
  
  #-- sort 
  arsort($sorted);
    
  $displayed  = 0;//$displayed will count pages successfully displayed
  
  #-- gen output
  $o = "";
  foreach ($sorted as $id=>$uu) {
  
    $row = ewiki_db::GET($id);
  
    #-- require auth
    if (EWIKI_PROTECTED_MODE && !ewiki_auth($id, $row, "view", $ring=false, $force=0)) {
       if (EWIKI_PROTECTED_MODE_HIDING) {
          continue;
       } else {
          $row["content"] = ewiki_t("FORBIDDEN");
       }
    }
    
    $text = "\n".substr($row["content"], 0, $n_len);
    $text = str_replace("[internal://", "[  internal://", $text);
    $text .= " [...[read more | $id]]\n";
    
    #-- title mangling (from ewiki.php)
    $title=$id;      
    if ($ewiki_config["split_title"] && $may_split) {
      $title = ewiki_split_title($title, $ewiki_config["split_title"], 0&($title!=$ewiki_title));   //Why 0&?
    }
    else {
      $title = htmlentities($title);
    }      
    if ($pf_a = @$ewiki_plugins["title_transform"]) {
      foreach ($pf_a as $pf) { $pf($id, $title, $go_action); }
    }
    
    if($ewiki_config["wm_publishing_headers"]){
      $text = preg_replace("/^!([^!])/m","!! \$1",$text);
      $o .= "\n" .
          "! [\"$title\"$id]";
    }else{
      $text = preg_replace("/^!!!/m","!!",$text);
      $o .= "\n" .
          "!!! [\"$title\"$id]";      
    }
    $o .=" µµ". strftime(ewiki_t("LASTCHANGED"), $row["lastmodified"])."µµ\n";
    $o .= " $text\n";
    $o .= "----\n";
  
    if (!($n_num--)) {
       break;
    }
  }
  
  
  #-- render requested wiki page  <-- goal !!!
  $render_args = array(
    "scan_links" => 1,
    "html" => (EWIKI_ALLOW_HTML||(@$data["flags"]&EWIKI_DB_F_HTML)),
  );
   $o =  $ewiki_plugins["render"][0] ($o, $render_args);
  
  return($o);
}

?>
