<?php

/*
The announcements plugin is a handler that displays pages from a list rather 
than the requested page.  I recommend and have configured the default for this 
plugin to use the lib/delegator plugin.  A user data plugin is required 
for this feature.

Written By:  Andy Fundinger (Andy@burgiss.com)
*/

$ewiki_plugins["delegator"]["LoginDelegator"][]="ewiki_announcements";

//Add an ordered list of pages to present as announcements 
//all pages should have a default value of 0.   This list will be added
//to each user as they view announcements for the first time and cannot been
//altered after that (except through AdminFullUser or other plugins)
$ewiki_config["DefaultNotify"]=  array(EWIKI_PAGE_INDEX => 0);

function ewiki_announcements($id, $data, $action){
  global $ewiki_plugins, $ewiki_config;

  if(!isset($GLOBALS['ewiki_auth_user'])){
    return;
  }

  $notifyDates=ewiki_get_uservar("NotifyDates",FALSE);

  if(!$notifyDates){
    $notifyDates=$ewiki_config["DefaultNotify"];
  }else{
    $notifyDates=unserialize($notifyDates);
  }

  foreach($notifyDates as $pageName=>$date){    
    $data=ewiki_db::GET($pageName);

    if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($pageName, $data, "view")) {
        continue;
    }   
    
    if($data['lastmodified']>$date){
      $dispDate=$data['lastmodified'];
      $dispPage=$pageName;    
      break;
    }
  }

  if(!isset($dispPage))
    return; 
    
  $notifyDates[$dispPage]=$dispDate;
  ewiki_set_uservar("NotifyDates",serialize($notifyDates));
  
  $o=ewiki_page('view/'.$dispPage);
    
  //page_final plugins have been run, unset them
	unset($ewiki_plugins["page_final"]);    
  
  return($o);

}

?>