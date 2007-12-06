<?
/* gets html inputs, truncates them to a specified length, and reinserts them into the $_REQUEST variable
   inputs not found in the $ewiki_input_limits array are unset.
   written by: Jeffrey Engleman
*/

//copied from ewiki.php so their value is defined here.
	define("EWIKI_UP_PAGENUM", "n");	# _UP_ means "url parameter"
	define("EWIKI_UP_PAGEEND", "e");
  define("EWIKI_UP_PAGE_LENGTH", 3);  //allows up to 999 records

define("EWIKI_USERNAME_LENGTH", 80);
define("EWIKI_GROUPNAME_LENGTH", 32);
define("EWIKI_PASSWORD_LENGTH", 32);
define("EWIKI_FIELDNAME_LENGTH", 32);
//array(PAGENAME => array(INPUTNAME => INPUTLENGTH, INPUT2NAME => INPUT2LENGTH));
//special PAGENAME entries include:
//_AllPages: handles submits that can appear on all pages e.g. username and password
//_Binary: handles submits that appear on binary pages e.g. internal://....
//_Edit: handles submits from pages prefixed with edit/ and updateformatheader/
$ewiki_input_limits=array(
"_AllPages" =>            array("username" => EWIKI_USERNAME_LENGTH, "password" => EWIKI_PASSWORD_LENGTH, "submit_login_img_x" => 2, 
                            "submit_login_img_y" => 2, "submit_login" => 5, "thankyou" => 1, "id" => 160, "page" => 160, "PHPSESSID" => 32,
                            "i_am_no_spambot" => 12, "new_filename" => 160, "comment" => 1600000, "section" => 160, "year" => 4,"version" => 3,
                            EWIKI_UP_PAGENUM => EWIKI_UP_PAGE_LENGTH,EWIKI_UP_PAGEEND => EWIKI_UP_PAGE_LENGTH),
"_Binary" =>              array("binary" => 160),
"_Manage" =>              array("submit_manage" => 0, "liveuserPermsView" => 10,"liveuserPermsPublish" => 10),
"_Email" =>               array("email_address" => 340, "email_text" => 255,"not_first_time" => 1,"email_page" => 1),
"_Edit" =>                array("piclogocntrlSelectLogo" => 160, "pageimagecntrl" => 160, "encoded_email" => 0, "go" => 0, "preview" => 7, 
                            "content" => 1677215 , "save" => 4, "liveuserPermsView" => 10, "liveuserPermsEdit" => 10, 
                            "liveuserPermsPublish" => 10),
"WikiDump" =>             array("dump_id" => 160, "download_tarball" => 17, "dump_images" => 1, "dump_fullhtml" => 1, "dump_virtual" => 1,
                            "dump_depth" => 3, "dump_arclevel" => 1, "dump_arctype" => 3),
"ExAllTodo" =>            array("q" => 16),
"ExAllPolicy" =>          array("q" => 16),
"Search" =>               array("Submit_x" => 2, "Submit_y" => 2, "q" => 50, "where" => 7),
"SearchPages" =>          array("q" => 50),
"PowerSearch" =>          array("q" => 50, "where" => 7),
"AdminAddUsers" =>        array("username_text" => EWIKI_USERNAME_LENGTH, "text_E-Mail_Address" => EWIKI_USERNAME_LENGTH, 
                            "group_list" => EWIKI_GROUPNAME_LENGTH, "submit_addusers" => 1, "text_" => 255),
"AdminPerms" =>           array("submit_changeperm" => 14, "ring_" => 3, "chk_" => 2, "letterfilter" => 5, "pagefilter" => 160,"classfilter" => 10,
                            "submit_filterperm" => 6, "pagename_text" => 160, "ring_list" => 3, "right_list" => 3, "submit_addperm" => 17),
"AdminPermsReport" =>     array("letterfilter" => 5, "pagefilter" => 160, "classfilter" => 10, "submit_filterperm" => 6),
"AdminRights" =>          array("chk_" => 2, "submit_changerights" => 14, "rightname_text" => 50, "addgroup" => 2, "submit_addright" => 9),
"AdminUsers" =>           array("chname_" => EWIKI_USERNAME_LENGTH, "origname_" => EWIKI_USERNAME_LENGTH, "usernames_text" => EWIKI_USERNAME_LENGTH*10, 
                            "username_text" => EWIKI_USERNAME_LENGTH, "pw_text" => EWIKI_PASSWORD_LENGTH, "search_fieldname" => EWIKI_FIELDNAME_LENGTH,
                            "chgroupname_" => EWIKI_GROUPNAME_LENGTH, "usernames_grouplist" => EWIKI_GROUPNAME_LENGTH, 
                            "grouplist" => EWIKI_GROUPNAME_LENGTH, "groupname_text" => EWIKI_GROUPNAME_LENGTH,"origgroupname_" => EWIKI_GROUPNAME_LENGTH,
                            "uvar_fieldname" => EWIKI_FIELDNAME_LENGTH,"chpw_" => EWIKI_PASSWORD_LENGTH,"grouplist" => EWIKI_GROUPNAME_LENGTH,
                            "submit_removeusersfromgroup" => 15, "radpw_" => 6,"chkgroup_" => 2, "chk_" => 2, "submit_deleteusers" => 16, 
                            "submit_changegroups" => 14,  "submit_adduser" => 8, "chuvar_" => 255, "origchuvar_" => 255,  "search_fieldvalue" => 255,
                            "submit_searchaccount" => 6, "submit_addusers" => 9,"pwgen_addusers" =>2,"addright" => 2, "submit_addgroup" => 9,
                            "submit_adduserstogroup" => 12, "chkrandpw_"=> 2,"submit_changeusers" => 14), 
"AdminFullUser" =>        array("accountname_text" => EWIKI_USERNAME_LENGTH,"new_accountfield" => EWIKI_FIELDNAME_LENGTH, "submit_viewaccount" => 9, 
                            "chk_" => 2, "text_" => 255, "submit_changeaccount" => 14,"submit_clearuservars" => 14,  "new_accountfieldvalue" => 255, 
                            "submit_accountaddfield" => 9,"batch_fieldnames" => 1649, "batch_fieldvalues" => 12799, "submit_batchfields" => 10, 
                            "submit_batchusers" => 10, "bulk_items" => 1677215, "submit_bulkset" => 11, "batch_usernames" => 1649),
"UserInfo" =>             array("submit_changeaccount" => 14, "text_" => 255),
"AdminSearchAccounts" =>  array("search_fieldname" => EWIKI_FIELDNAME_LENGTH, "chk_" => EWIKI_FIELDNAME_LENGTH, "search_fieldvalue" => 255, 
                            "submit_searchaccount" => 6,"text_" => 255, "submit_changeaccount" => 14, "submit_clearuservars" => 15),
"ChangePassword" =>       array("oldpassword" => EWIKI_PASSWORD_LENGTH, "newpassword1" => EWIKI_PASSWORD_LENGTH, "newpassword2" => EWIKI_PASSWORD_LENGTH, 
                            "submit" => 15),
"TextUpload" =>           array("textfile_overwrite_pages" => 1, "textfile_assume_text" => 1, "textfile_noext_is_text" => 1,
                            "textfile_brute_force" => 1, "textfile_brute_force" => 1, "textfile_saveas" => 160, 
                            "textfile_strip_ext" => 1, "upload_text_file" => 300),
"ProtectedEmail" =>       array("encoded_email" => 340),
"Login" =>                array("cancel_login" => 6));

$ewiki_plugins["init"][-4] = "ewiki_input_truncate";

function ewiki_input_truncate(){
  global $ewiki_input_limits, $ewiki_plugins;

  //get and trim current page id
  $id=substr(ewiki_id(), 0, $ewiki_input_limits['_AllPages']['id']);
  
  if ($delim = strpos($id, EWIKI_ACTION_SEP_CHAR)) {
      $action = substr($id, 0, $delim);
      $id = substr($id, $delim + 1);
  }

  foreach($_REQUEST as $key => $value){ //loop through the $_REQUEST variable
    $input_value=trim($value); //trim value 

    $ewiki_input_key=$key;
    $ewiki_input_id=ewiki_check_input($id, $ewiki_input_key, $action);
    if(!strlen($ewiki_input_id)){
      $ewiki_input_key=ewiki_reset_key($id, $key);
      $ewiki_input_id=ewiki_check_input($id, $ewiki_input_key, $action);
    }
    if(!strlen($ewiki_input_id)){
      ewiki_log('Unhandled submit: Page: "'.$id.'" Key: "'.$key.'" Value: "'.$value.'" \n', 1);
      ewiki_set_globals($key);
    }
    
    if(is_array($input_value)){
      //loop through the input array 
      foreach($input_value as $array_input_key => $array_input_value){
        $input_value=trim($array_input_value); //redefine input_value with the array value
        //check to see if its longer than allowed
        if(strlen($input_value)>$ewiki_input_limits[$ewiki_input_id][$ewiki_input_key]){
          //its too long truncate it...
          ewiki_set_globals($key, substr($input_value, 0, $ewiki_input_limits[$ewiki_input_id][$ewiki_input_key]), $array_input_key);
        }
      }
    }
    
    //if the input length is longer than its supposed to be trim it.
    elseif((strlen($input_value)>$ewiki_input_limits[$ewiki_input_id][$ewiki_input_key]) && isset($ewiki_input_limits[$ewiki_input_id][$ewiki_input_key])){
      ewiki_log("Trimming: Key: $ewiki_input_key Id: $ewiki_input_id to length: ".$ewiki_input_limits[$ewiki_input_id][$ewiki_input_key]);
      ewiki_set_globals($key, substr($input_value, 0, $ewiki_input_limits[$ewiki_input_id][$ewiki_input_key]));
    }
  }
}

function ewiki_check_input($id, $ewiki_input_key, $action){
    global $ewiki_input_limits;
    //determines what type of page we are running on and sets the ewiki_input_id variable accordingly
    //check to see if our key matches up with an input for this specific page
    if(isset($ewiki_input_limits[$id][$ewiki_input_key])){
      //some inputs are arrays themselves this handles that.
      return $id;
    //else check to see if it's a global input
    } elseif(isset($ewiki_input_limits["_AllPages"][$ewiki_input_key])){
      return "_AllPages";
      //$maxlen=$ewiki_input_limits["_AllPages"][$ewiki_input_key];
    //else check to see if its a binary input  
    } elseif(isset($ewiki_input_limits["_Binary"][$ewiki_input_key]) && strstr($action, "binary")){
      return "_Binary";
      //$maxlen=$ewiki_input_limits["_Binary"][$ewiki_input_key];
    //else check to see if its an edit input  
    } elseif(isset($ewiki_input_limits["_Email"][$ewiki_input_key]) && ($action=="emailpage")){
      return "_Email";
      //$maxlen=$ewiki_input_limits["_Edit"][$ewiki_input_key];
    } elseif(isset($ewiki_input_limits["_Edit"][$ewiki_input_key]) && ($action=="edit"  || $action=="updateformatheader")){
      return "_Edit";
      //$maxlen=$ewiki_input_limits["_Edit"][$ewiki_input_key];
    } elseif(isset($ewiki_input_limits["_Manage"][$ewiki_input_key]) && ($action=="manage")){
      return "_Manage";
      //no more input types. fail.
    } else {
      return ""; 
    }
}

//Resets a key to a substring of itself to handle iteratively generated inputs.
function ewiki_reset_key($id, $key){
    global $ewiki_input_limits;
    //handles multiple elements with the same prefix *_ and a numerical suffix
    if(preg_match("/(\D+_)(\d+)/", $key, $matches)){
      //redefine the input key as the prefix of these elements *_
      return $matches[1];
    //handles multiple elements with the same prefix *_ and a non numeric suffix
    } elseif(preg_match("/([a-zA-Z]+_)(.*)/", $key, $matches) && !isset($ewiki_input_limits[$id][$key]) && !isset($ewiki_input_limits["_AllPages"][$key])){
      return $matches[1];
    } else {
      return ""; //used to match the request key to the ewiki_input_limits array
    }
}

/**ewiki_set_globals sets or clears global HTTP variables as requested
 *
 * @param string key
 * @param string $newval
 */
function ewiki_set_globals($key, $newval="", $key2=""){ 
  for($i=1;$i<=4;$i++){
    switch ($i){
      case 1:  //process $_REQUEST
        if($newval=="" && $key2==""){
          unset($_REQUEST[$key]);
        } elseif($newval=="" && $key2!=""){
          unset($_REQUEST[$key][$key2]);
        } elseif($key2=="") {
          $_REQUEST[$key]=$newval;
        } else {
          $_REQUEST[$key][$key2]=$newval;
        }
        break;
      case 2: //process $_POST
        if($newval=="" && $key2==""){
          unset($_POST[$key]);
        } elseif($newval=="" && $key2!=""){
          unset($_POST[$key][$key2]);
        } elseif($key2=="") {
          $_POST[$key]=$newval;
        } else {
          $_POST[$key][$key2]=$newval;
        }
        break;
      case 3: //process $_GET
        if($newval=="" && $key2==""){
          unset($_GET[$key]);
        } elseif($newval=="" && $key2!=""){
          unset($_GET[$key][$key2]);
        } elseif($key2=="") {
          $_GET[$key]=$newval;
        } else {
          $_GET[$key][$key2]=$newval;
        }
        break;
      case 4: //process $_COOKIE
        if($newval=="" && $key2==""){
          unset($_COOKIE[$key]);
        } elseif($newval=="" && $key2!=""){
          unset($_COOKIE[$key][$key2]);
        } elseif($key2=="") {
          $_COOKIE[$key]=$newval;
        } else {
          $_COOKIE[$key][$key2]=$newval;
        }
        break;
    }
  }
}

?>