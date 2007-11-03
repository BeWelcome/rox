<?php

/*
The user data collector is a handler that displays a request for user data rather
than the requested page.  I recommend and have configured the default for this 
plugin to use the lib/delegator plugin.  A user data plugin and the UserInfo page
(in module/uservars_gui) are required for this feature.

Written By:  Andy Fundinger (Andy@burgiss.com)
*/

$ewiki_plugins["delegator"]["LoginDelegator"][]="ewiki_uservar_collect";

$ewiki_config["CollectedUserData"]=  array("First Name" => "","Last Name" => "",
  "Company" => "","E-Mail Address" => "", "Phone Number" => "",
  );

function ewiki_uservar_collect($id, $data, $action){
  global $ewiki_plugins, $ewiki_config;

  if(!isset($GLOBALS['ewiki_auth_user'])){
    return;
  }
  
  //Authenticate on the UserInfo page that we will be submitting to.
  if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth('UserInfo', $uu, "view")) {
    return;
  } 

  foreach($ewiki_config["CollectedUserData"] as $checkVar=>$uu){    
    if(strlen(ewiki_get_uservar($checkVar))==0){
      $currVar=$checkVar;
      break;
    }
  }

  if(isset($currVar)){        
    $o = ewiki_make_title($id, "User data update", 2); 
    $o .= "Our database does not include your $currVar, please enter it below:";
    $o .= '<p><form method="post" action="'.ewiki_script('UserInfo').'"><table border="1" cellpadding="1">';
    $o .= '<tr><th>'.$currVar.'</th><td><input name="text_'.$currVar.'" type="text"></td></tr>';
    $o .= '<tr><td></td><td><input value="Submit Changes" type="submit" name="submit_changeaccount" /></td></tr>';
    $o .= '</table></form>';  
    return($o);
  }
}

?>