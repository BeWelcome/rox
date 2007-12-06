<?php

/*

Displays all calendar entries for a page as an action plugin. 

Requires the subpages plugin.

by AndyFundinger

*/

$ewiki_plugins["action"]['calendarlist'] = "ewiki_action_calendar_list";
$ewiki_t["en"]["NOCALENDAR"] = "There are no calendar entries for this page.";		


function ewiki_action_calendar_list($id, $data, $action) {
  $pages=ewiki_subpage_list($id,CALENDAR_NAME_SEP);
  
  foreach($pages as $pageId=>$uu){
    if(! ewiki_isCalendarId($pageId)){
      unset($pages[$pageId]);
    }
  }
  
  if(0==count($pages)){
    return(ewiki_t("NOCALENDAR"));
  }else{
    //ksort($pages);
    $o = '<div class="calendar_list"><small>'.ewiki_t('CALENDERFOR')." $id:</small><br />";
    $o .= ewiki_list_pages($pages)."</div>\n";   
  }
  
  return($o);
}

?>