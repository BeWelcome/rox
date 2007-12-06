<?php

/*

*/

  //override to use old standard
//define('EWIKI_USERVARS_PAGENAME_PREFIX','UserDataPage_');
define('EWIKI_USERVARS_PAGENAME_PREFIX','system/user/');

$ewiki_plugins['uservars_store'][]="ewiki_uservars_pages_store";
$ewiki_plugins['uservars_retrieve'][]="ewiki_uservars_pages_retrieve";
$ewiki_plugins["login_handler"][]="ewiki_uservars_pages_retrieve";

$ewiki_plugins['uservars_get'][]="ewiki_uservars_pages_getvar";
$ewiki_plugins['uservars_set'][]="ewiki_uservars_pages_setvar";
$ewiki_plugins['uservars_getall'][]="ewiki_uservars_pages_getallvar";
$ewiki_plugins['uservars_clear'][]="ewiki_uservars_pages_clear";
$ewiki_plugins['uservars_search'][]='ewiki_uservars_pages_search';

/*
$ewiki_plugins['page']['Test']         = 'ewiki_page_test';
function ewiki_page_test($id, $data)
{
    global $ewiki_uservars;

    ob_start();
/*

    var_dump(ewiki_uservars_pages_retrieve());    
    $ewiki_uservars['Real Name']='Andy Fundinger';
    var_dump(ewiki_uservars_pages_retrieve());    

    ewiki_uservars_pages_store();
    var_dump(ewiki_uservars_pages_retrieve());    
// /


    echo("<p>".ewiki_uservars_getvar('Real Name').'</p>');
    echo("<p>".ewiki_uservars_getvar('Fake Name','No alias').'</p>');
    ewiki_uservars_setvar('Real Name',"Andy");
    ewiki_uservars_setvar('Fake Name',"Andriod");
    echo("<p>".ewiki_get_uservar('Real Name').'</p>');
    
    $o = ob_get_contents();
    ob_end_clean();
    return $o;    
}
// */

function ewiki_getall_uservar($username=NULL){
  global $ewiki_plugins;

  #-- plugins to call
  $pf = @$ewiki_plugins['uservars_getall'][0];
   
  if ($pf && function_exists($pf)) {
    $r=$pf($username);
    if($r){
      ewiki_log('UserVars getall_uservar: uservariables successfully retrieved for "'.$username.'"', 3);
      return($r);
    } else {
      ewiki_log('UserVars: there was an error retrieving uservariables for "'.$username.'"', 3);
    }
  }
  return(array());
}

function ewiki_clear_uservar($varname, $username=NULL){
  global $ewiki_plugins;

  #-- plugins to call
  $pf = @$ewiki_plugins['uservars_clear'][0];
  
  $varname=trim(strtr($varname, "_", " "));
  
  if ($pf && function_exists($pf)) {
    $r=$pf($varname, $username);
    if($r){
      ewiki_log('UserVars clear_uservar: "'.$varname.'" successfully cleared for user "'.$username.'"', 3);
      return($r);
    } else {
      ewiki_log('UserVars clear_uservar: error clearing"'.$varname.'" for user "'.$username.'"', 3);
    }
  }
  return(FALSE);
}

function ewiki_search_uservar($varname, $value=NULL){
  global $ewiki_plugins;

  #-- plugins to call
  $pf = @$ewiki_plugins['uservars_search'][0];
  
  
  if ($pf && function_exists($pf)) {
    //echo("ewiki_search_uservar calling $pf($varname,$value)");
    $r=$pf($varname,$value);
    if($r){
      ewiki_log('UserVars search_uservar: search for "'.$value.'" in field "'.$varname.'" was successful', 3);
      return($r);
    } else {
      ewiki_log('UserVars search_uservar: search for "'.$value.'" in field "'.$varname.'" failed', 3);
    }
  }
  return(array());
}

function ewiki_get_uservar($varname, $defaultValue=NULL, $username=NULL){
    global $ewiki_plugins;

    #-- plugins to call
    $pf = @$ewiki_plugins['uservars_get'][0];
    
    
    if ($pf && function_exists($pf)) {
      $r=$pf($varname, $defaultValue, $username);
      if($r){
        ewiki_log('UserVars get_uservar: get "'.$varname.'" for user "'.$username.'" was successful', 3);
        return($r);
      } else {
         ewiki_log('UserVars get_uservar: get "'.$varname.'" for user "'.$username.'" failed', 3);
      }
    }
    return($defaultValue);
}
function ewiki_set_uservar($varname, $value, $username=NULL){
    global $ewiki_plugins;

    $varname=trim(strtr($varname, "_", " "));
    #-- plugins to call
    $pf = @$ewiki_plugins['uservars_set'][0];
    
    
    if ($pf && function_exists($pf)) {
        $r=$pf($varname, $value, $username);
        if($r){
          ewiki_log('UserVars set_uservar: seting "'.$varname.'" to "'.$value.'" for user "'.$username.'" was successful', 3);
          return($r);
        } else {
          ewiki_log('UserVars set_uservar: seting "'.$varname.'" to "'.$value.'" for user "'.$username.'" failed', 3);
        }

    }
    return(FALSE);
}

/*
Gets all variables for $username or the current user if $username
is not set.
*/
function ewiki_uservars_pages_getallvar($username=NULL){
    return(ewiki_uservars_pages_retrieve($username));
}

/*
Gets the variable $varname for $username or the current user if $username
is not set.  If $varname is not set $defaultValue is returned.
*/
function ewiki_uservars_pages_getvar($varname, $defaultValue=NULL, $username=NULL){
    $data=ewiki_uservars_pages_retrieve($username);
    if(isset($data[$varname])){
        return($data[$varname]);
    }else{
        return($defaultValue);
    }
}

function ewiki_uservars_pages_clear($varname, $username=NULL){
  $data=ewiki_uservars_pages_retrieve($username);
  unset($data[$varname]);
  return(ewiki_uservars_pages_store($data,$username));
}

function ewiki_uservars_pages_search($varname, $value=NULL){

  $result = ewiki_db::SEARCH("content", $varname);
  
  //Get a list of all pages containing our varname
  while ($row = $result->get()) {            
    if(!preg_match("/".EWIKI_USERVARS_PAGENAME_PREFIX."(.*)/",$row["id"],$matches)){
      continue;
    }//Page name is properly formed as a UserVars page name
    $username=$matches[1];
    
    //echo("Checking $username for $varname");
    if(!($data=ewiki_uservars_pages_retrieve($username))){
      //echo(" rejected, no data retrieved");
      continue;
    }//data was retrieved from the page. 

    if(!isset($data[$varname])){
      //echo(" rejected, variable not set $varname");
      continue;
    }//The varname we are searching for was there
    
    if(isset($value)&&($value!=$data[$varname])){
      //echo("rejected $value != ".$data[$varname]);
      continue;
    }//if we were looking for a value, it matches
    
    //echo("passed, setting $data[$varname] to $values[$varname]");
    $values[$username]=$data[$varname];
  }

  return($values);
}

/*
Sets the variable $varname to $value for $username or the current user if $username
is not set.
*/
function ewiki_uservars_pages_setvar($varname, $value, $username=NULL){
    $data=ewiki_uservars_pages_retrieve($username);
    $data[$varname]=$value;
    return(ewiki_uservars_pages_store($data,$username));
}

/*
Stores $data as the user variables for $username returning sucess and storing it in 
global $ewiki_uservars if you are storing the user data for the current user.

$username is optional, $GLOBALS['ewiki_auth_user']) is assumed.
$data is optional, global $ewiki_uservars is stored if none is passed.
*/
function ewiki_uservars_pages_store($data=NULL, $username=NULL){
    global $ewiki_uservars,$ewiki_errmsg;

    if(!isset($username)){
        $username = $GLOBALS['ewiki_auth_user'];        
    }
    if($username == $GLOBALS['ewiki_auth_user']){
        if(!isset($data)){            
            $data=$ewiki_uservars;        
        }else{
            $ewiki_uservars=$data;
        }
    }
    
    //echo("<p>Storing user variables for $username</p>");
    $oldpage=ewiki_db::GET(EWIKI_USERVARS_PAGENAME_PREFIX.$username);

    //if there was already an existing, non-system page, fail out.
    if(($oldpage['version']>0)&&(!($oldpage["flags"] & EWIKI_DB_F_SYSTEM))){
        $ewiki_errmsg=ewiki_t('ERRORSAVING');
        return(0);
    }

    $save = array(
               "id" => EWIKI_USERVARS_PAGENAME_PREFIX.$username,
               "version" => @$oldpage["version"] + 1,
               "flags" => EWIKI_DB_F_SYSTEM,
               "content" => serialize($data),
               "author" => ewiki_author(),
               "lastmodified" => time(),
               "created" => ($uu=@$oldpage["created"]) ? $uu : time(),
               "meta" => ($uu=@$oldpage["meta"]) ? $uu : "",
               "hits" => ($uu=@$oldpage["hits"]) ? $uu : "0",
            );    

    return(ewiki_db::WRITE($save));
}

/*
Retrieves the userdata for $username returning it and storing it in 
global $ewiki_uservars if you are retrieving the user data for the current user.

$username is optional, $GLOBALS['ewiki_auth_user']) is assumed.
*/
function ewiki_uservars_pages_retrieve($username=NULL){
    global $ewiki_uservars;
    if(!isset($username)){
      $username = $GLOBALS['ewiki_auth_user'];        
    }

    if(!empty($ewiki_uservars)&&($username == $GLOBALS['ewiki_auth_user'])){
      return($ewiki_uservars);
    }

    $data=ewiki_db::GET(EWIKI_USERVARS_PAGENAME_PREFIX.$username);
    
    //User data must be on system pages
    if($data["flags"] & EWIKI_DB_F_SYSTEM){
        //echo("System flag set ");
        $userdata=unserialize($data['content']);        
    }
    //log and fail if no userdata found i.e. no page, no system flag, or not an array
    if(!is_array($userdata)){
        //echo(" retrieved no user data");
        ewiki_log("No userdata for $username in ewiki_uservars_pages_retrieve()",2);        
        return(array());
    }
        
    if($username == $GLOBALS['ewiki_auth_user']){
        $ewiki_uservars=$userdata;
    }

    return($userdata);
}

?>