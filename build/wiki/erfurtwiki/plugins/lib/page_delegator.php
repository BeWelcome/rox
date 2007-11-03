<?php

/* Calls a series of ewiki handlers until one returns a page, if no page is 
returned serves pagename_default.

Written By:  Andy Fundinger (Andy@burgiss.com)
*/

$ewiki_config['LoggedInPage']='LoginDelegator';
$ewiki_plugins["page"]["LoginDelegator"] = "ewiki_page_delegator";
$ewiki_config["delegator_default"]["LoginDelegator"]="Welcome";

function ewiki_page_delegator($id, $data, $action) {
  global $ewiki_plugins,$ewiki_config;

  #-- handlers
  $handler_o = "";
  if ($pf_a = @$ewiki_plugins["delegator"][$id]) {
    ksort($pf_a);
    foreach ($pf_a as $pf) {
      if ($handler_o = $pf($id, $data, $action)) { break; }
    }
  }
  
  if(isset($handler_o)){
    return($handler_o);
  }

  //Authentication for the default page is handled inside ewiki_page
  // this may result in an access denied page.
  $o=ewiki_page('view/'.$ewiki_config["delegator_default"][$id]);
    
  //page_final plugins have been run, unset them
	unset($ewiki_plugins["page_final"]);    
  
  return($o);
}


?>