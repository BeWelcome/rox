<?php

/*
   Description: prints out a wiki-news style summary of blog entries for   the current  page.

   The Calendar plugin should be included for page creation and archival browsing.
   
   Adapted from:  Wikinews
   Developed by AndyFundinger 
   Extended by WojciechJanKalka <w@kalka.org>

   Usage:
   <?plugin Blog
            page=MyBlogPage  // Page ID
            num=20           // List how many Items
            len=512          // List length till more
	    sort=0           // Sort order  1 ->pagename or 0 ->entrydate
	    sort_reverse=0   // Reverse sort 
	    calendar=0       // Normal blog or calendar blog (0/1)
	    hr=0             // Horizontal Rule each item (0/1)
            more=0           // Show more link to blog page (0/1)
   ?>
  
   1.1.4 - cleaned up the code, added documentation (wjk)
   1.1.3 - +added sort on and reverse sort (wjk)
           +added calendar blog choice (wjk) *untested*
	   +added some other boolean switches (wjk)
   1.1.2 - changed the plugin from calendarlist to blog (wjk)
           +added ewiki_blog_list (wjk)
   1.1.1 - started this plugin (wjk)
*/
 

// display a list of blog itmes 
function ewiki_blog_list($n_num,$n_len,$b_sort,$b_sortrev,$b_hr,$b_more,$c_regex){
  global $ewiki_plugins, $ewiki_config;

  #-- fetch all page entries from DB, for sorting on pagename or lastmodified
  if ($b_sort) {
    $result = ewiki_db::GETALL(array("pagename"));
  } else {
    $result = ewiki_db::GETALL(array("lastmodified"));
  }
  
  $sorted = array();

  // get an array from the database
  while ($row = $result->get(0, 0x0137, EWIKI_DB_F_TEXT)) {
  
      if ($c_regex && !preg_match($c_regex, $row["id"])) {
        continue;
      }
      
      if ($b_sort) { 
      $sorted[$row["id"]] = $row["pagename"];
      } else {
      $sorted[$row["id"]] = $row["lastmodified"];
      }
  }
  
  #-- sort normal or reversed
  if ($b_sortrev) {
  asort($sorted);
  } else {
  // if we sort on lastmodified reverse sort
  arsort($sorted);
  }
    
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
    
    #-- shore more link or not 
    if ($b_more) {
    $text .= " [...[read more | $id]]\n";
    }
    
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
    
    // add a horizontal line if wanted 
    if ($b_hr) { 
    $o .= "----\n";
    }
  
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

$ewiki_plugins["mpi"]["blog"] = "ewiki_mpi_blog";

// the mpi funcion call
function ewiki_mpi_blog($action="html", $args, &$iii, &$s) {

  global $ewiki_config;

  // read parameters or use default values
 ($id = $args["page"]) or ($id = $GLOBALS["ewiki_id"]); 
 ($n_num = $args["line"]) or ($n_num = 20); 
 ($n_len = $args["length"]) or ($n_len = 512);
 ($b_hr = $args["hr"]) or ($b_hr = 0);
 ($b_sort = $args["sort"]) or ($b_sort = 0);
 ($b_calendar = $args["calendar"]) or ($b_calendar = 0);
 ($b_more = $args["displaymore"]) or ($b_more = 0);
 ($b_sortrev = $args["sort_reverse"]) or ($b_sortrev = 0);

  // if we use the plugin for the calendar, quit on no  calendar pages 
  if ( (!calendar_exists(false)) && ($b_calendar) ){
    return;
  }

   // the output 
   $o='<div class="text-blog">'
    //  .'DEBUG -> '.' Line = '.$hr.' Sort = '.$sort.' Reverse = '.$b_sortrev
      .ewiki_blog_list($n_num,$n_len,$b_sort,$b_sortrev,$b_hr,$b_more,'/^'.$id.EWIKI_NAME_SEP.'\d{8}$/')
      . '</div>';

  return($o);
}


?>