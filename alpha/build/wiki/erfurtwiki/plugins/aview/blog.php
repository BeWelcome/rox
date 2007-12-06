<?php

/*
   prints out a wiki-news style summary of the calendar entries for the current
   page as an aview plugin.  The most reccent 10 entries are shown by default.
   
   The Calendar plugin should be included for page creation and archival browsing.
   
   Adapted from:  Wikinews
   Developed by AndyFundinger 
   
*/
 
 
$ewiki_plugins["view_append"][] = "ewiki_view_append_blog";

include_once("plugins/page/wikinews.php");

function ewiki_view_append_blog($id, $data, $action) {
  global $ewiki_config;

  if(!calendar_exists(false)){
    return;
  }

   #-- conf
   ($n_num = $ewiki_config["wikinews_num"]) || ($n_num = 10);
   ($n_len = $ewiki_config["wikinews_len"]) || ($n_len = 512);
   
   $o='<div class="text-blog">'
      .ewiki_wikinews_summary($n_num,$n_len,'/^'.$id.EWIKI_NAME_SEP.'\d{8}$/')
      . '</div>';
   $o.='<a href="'.ewiki_script("calendarlist", $id).'">View all log entries.</a>';

  return($o);
}



?>
