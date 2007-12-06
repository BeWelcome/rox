<?php

/*
Interfaces to user variables, 

*/

$ewiki_user_gui_fields = array(
  "First Name" => "", "Middle Name" => "","Last Name" => "","Title" => "",
  "Company" => "", "E-Mail Address" => "", "Phone Number" => "",
  "Address" => "", "City" => "", "State" => "","Zip Code" => "",
  "Country" => ""  
  );
$ewiki_t["en"]["USERDATAUPDATESUCCESS"] = "Your user data has been successfully updated.";

/*
  Add the pages you would like to use to your config file.
*/
 $ewiki_plugins["page"]["AdminFullUser"]="ewiki_page_uservars_full";
 
 //The current search function has a bug in it, some web browsers (Mozilla at least)
 //automatically convert "." to "_" in control names, use at your own risk.
 //$ewiki_plugins["page"]["AdminSearchAccounts"]="ewiki_page_uservars_search";
 $ewiki_plugins["page"]["UserInfo"]="ewiki_page_uservars_user";


/**admin fulluser gui for viewing all info about a specified user.
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */
function ewiki_page_uservars_search($id, $data, $action)
{
  global $ewiki_errmsg;
  
  ob_start();
  
  $success=TRUE; //used for displaying update status
  $editing=FALSE; //used for displaying update status
  
  echo ewiki_make_title($id, "Search User Data", 2); 
    
  if(isset($_REQUEST['search_fieldname'])){ //set fieldname variable
    $fieldname=$_REQUEST['search_fieldname'];
  }
  if(strlen($_REQUEST['search_fieldvalue'])){ //set fieldvalue variable
    $fieldvalue=$_REQUEST['search_fieldvalue'];
  }
  
  if(isset($_REQUEST['submit_clearuservars'])){ //check if the user clicked the "Delete Selected" button
    echo("Deleting user variables.... ");
    $editing=TRUE; //we are performing an edit
    foreach($_REQUEST as $request_key => $request_value){
      if(strstr($request_key, "chk_")){ //find all checkboxes
        $varname=substr($request_key,4); //strip chk_ from the beginning of the value
        $username=$request_value;
        if(!ewiki_clear_uservar($varname, $username)){ //clear the selected value
         $success=FALSE; //there was an error
        }
      }
    }
  }  
  
  if(isset($_REQUEST['submit_changeaccount'])){ //check if we are modifying an existing field
    $editing=TRUE; //we are performing an edit 
    foreach($_REQUEST as $request_key => $request_value){ 
      if(strstr($request_key, "text_")){ //find all text boxes
        $username=substr($request_key,5); //remove prefix text_ from value 
        if(!ewiki_set_uservar($fieldname, $request_value, $username)){ //set the desired information
          $success=FALSE;
        }
      }
    }
  }
  
  if($editing){ //check to see if we performed an edit 
    echo ($success ? ewiki_t("USERDATASUCCESS") : $ewiki_errmsg);  //if we did and there was an error display it, otherwise display success
  }
  
  if(!empty($fieldname)){
    $userdata = ewiki_search_uservar($fieldname, $fieldvalue); //get data for the given fieldname/fieldvalue combination
  }
    
  ?>
  <form method="post" action="">
    <table>
      <tr><td>Field Name</td><td><input type="text" name="search_fieldname"></td></tr>
      <tr><td>Value</td><td><input type="text" name="search_fieldvalue"></td></tr>
    </table>
    <input value="Search" type="submit" name="submit_searchaccount" />
  </form>

  <?
  
  if(is_array($userdata)){
    echo '<h4>Query Results</h4><form action="" method="post"><table border=1>';
    echo '<tr><th>Select</th><th>UserID</th><th>'.$fieldname.'</th></tr>';
    foreach($userdata as $username => $value){
      ?>
      <tr><td><input type="checkbox" name="chk_<?echo $fieldname?>" value="<?echo $username?>"></td><th><?echo $username?></th>
      <td><input type="text" name="text_<?echo $username?>" value="<?echo $value?>"></td></tr>
      <?
    }
    ?>
    </table>
    <input value="<?echo $fieldname?>" type="hidden" name="search_fieldname">
    <input value="<?echo $fieldvalue?>" type="hidden" name="search_fieldvalue">
    <input value="Submit Changes" type="submit" name="submit_changeaccount" />
    <input value="Delete Selected" type="submit" name="submit_clearuservars" />
    </form>
    <?
  }

  $o = ob_get_contents();
  ob_end_clean();
  return $o;  
}

/**admin fulluser gui for viewing all info about a specified user.
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */
function ewiki_page_uservars_full($id, $data, $action)
{
  global $ewiki_errmsg;
  $success=TRUE;
  $editing=FALSE;
  
  ob_start();
  
  // resolve username if none submitted use current username
  if(isset($_REQUEST['accountname_text'])){
    $user=$_REQUEST['accountname_text'];
  } else {
    $user =$GLOBALS['ewiki_auth_user'];
  }
  
  echo ewiki_make_title($id, "User info for $user", 2); 
  
  if(!empty($user)){
    // check to see if we are clearing fields
    if(isset($_REQUEST['submit_clearuservars'])){ //check if we are clearing values
      $editing=TRUE; //we are editing
      foreach($_REQUEST as $request_key => $request_value){
        if(strstr($request_key, "chk_") && $request_value ="on"){ //only use chk_ inputs
          $varname=substr($request_key,4); //remove the chk_ prefix
          if(!ewiki_clear_uservar($varname, $user)){ //clear the selected values
            $success=FALSE; //there was an error
          }
        }
      }
    } elseif(isset($_REQUEST['submit_changeaccount'])){ //check to see if we are editing fields
      $editing=TRUE;
      foreach($_REQUEST as $request_key => $request_value){
        if(strstr($request_key, "text_")){ //only work on fields with the text_ prefix
          $varname=substr($request_key,5); //remove the text_ prefix
          if(!ewiki_set_uservar($varname, $request_value, $user)){ //set appropriate data
            $success=FALSE;
          }
        }
      } //check to see if we are adding/editing a group of fields
    } elseif(isset($_REQUEST['submit_batchfields']) && !empty($_REQUEST['batch_fieldnames']) && !empty($_REQUEST['batch_fieldvalues'])){ 
      $editing=TRUE;
      $newfields = explode("\n", $_REQUEST['batch_fieldnames']); //split up the batch
      $newvalues = explode("\n", $_REQUEST['batch_fieldvalues']); //split up the batch
      foreach($newfields as $key => $field){
        $field=trim($field);
        $value=(isset($newvalues[$key])?trim($newvalues[$key]):"");
        if(strlen($field)){ //cannot add blank fieldnames
          if(!ewiki_set_uservar($field, $value, $user)){
            $success=FALSE;
          }
        }
      } //check if we are adding multiple users
    } elseif(isset($_REQUEST['submit_batchusers']) && !empty($_REQUEST['batch_usernames']) && !empty($_REQUEST['text_fieldname'])){ 
      $newusers=explode("\n", $_REQUEST['batch_usernames']);
      $field=trim($_REQUEST['text_fieldname']);
      $value=trim($_REQUEST['text_fieldvalue']);
      foreach($newusers as $username){
        $username=trim($username);
        if(!ewiki_set_uservar($field, $value, $username)){
          $success=FALSE;
        }
      }
    } elseif(isset($_REQUEST['submit_bulkset']) && strlen($_REQUEST['bulk_items'])){
      $editing=TRUE;
      $bulk=explode("\n", $_REQUEST['bulk_items']);
      foreach($bulk as $items){
        $values=explode(",", $items);
        $username=trim($values[0]);
        $varname=trim($values[1]);
        $value=trim($values[2]);
        if(strlen($varname)){
          if(!ewiki_set_uservar($varname, $value, $username)){
            $success=FALSE;
          }
        }
      }
    } elseif(isset($_REQUEST['submit_accountaddfield']) && !empty($_REQUEST['new_accountfield']) && !empty($_REQUEST['new_accountfield'])){
      $editing=TRUE; 
      if(!ewiki_set_uservar($_REQUEST['new_accountfield'], $_REQUEST['new_accountfieldvalue'], $user)){
        $success=FALSE;
      }
    }
  }
  
  if($editing){
    echo ($success ? ewiki_t("USERDATASUCCESS") : $ewiki_errmsg);
  }
  
  $account_uservars=ewiki_getall_uservar($user);

  ?>
  <form method="post" action="">
    <p>User Name <input type="text" name="accountname_text"><input value="View Info" type="submit" name="submit_viewaccount" /></p>
  </form>
  <h3>User Information</h3>
  <p><form method="post" action=""><table border="1" cellpadding="1">
  <?
  if(!empty($account_uservars)){
      foreach($account_uservars as $field => $value){
      echo '<tr><td><input type="checkbox" name="chk_'.$field.'"></td><th>'.$field.'</th>'.
           '<td><input name="text_'.$field.'" type="text" value="'.$value.'"></td></tr>';
      }
  }?>
	</table>
    <input value="Submit Changes" type="submit" name="submit_changeaccount" />
    <input value="Delete Selected" type="submit" name="submit_clearuservars" />
    <input value="<?echo $user?>" type="hidden" name="accountname_text">
  </form>
  <h3>Add/Edit a Field</h3>
  <form method="post" action="">
    <input value="<?echo $user?>" type="hidden" name="accountname_text">
    <table>
      <tr><td>Field Name</td><td><input type="text" name="new_accountfield"></td></tr>
      <tr><td>Value</td><td><input type="text" name="new_accountfieldvalue"></td></tr>
    </table>
    <input type="submit" value="Add Field" name="submit_accountaddfield" />
  </form>
  <h3>Add/Edit Multiple Fields</h3>
  <p>On the left insert one field name per line.  On the right insert
  a corresponding field value for the field.  These will be processed
  as a batch and each field will be created with its corresponding
  value.</p>
  <form method="post" action="">
    <input value="<?echo $user?>" type="hidden" name="accountname_text">
    <table>
      <tr><td><textarea name="batch_fieldnames" rows="10" cols="25"></textarea></td>
      <td><textarea name="batch_fieldvalues" rows="10" cols="25"></textarea></td></tr>
    </table>
    <input type="submit" name="submit_batchfields" value="Add Fields">
  </form>
  <h3>Add Field to Multiple Users</h3>
  <p>In the box below, insert one username per line.  Then insert a field
  name and an optional value for that field.  That field with that value
  will be added to all of the users in the box.</p>
  <form method="post" action="">
    <input value="<?echo $user?>" type="hidden" name="accountname_text">
    <table>
    <tr><td colspan=2>User Names:</td></tr><tr><td colspan=2><textarea name="batch_usernames" rows="10"  cols="24"></textarea></td></tr>
      <tr><td>Field Name</td><td><input type="text" name="text_fieldname"></td></tr><tr><td>Field Value</td><td><input type="text" name="text_fieldvalue"></td></tr>
    </table>
    <input type="submit" name="submit_batchusers" value="Add Fields">
  </form>
  <h3>Bulk Set</h3>
  <p>In the box below insert username,fieldname,value. Only one per line.</p>
  <form method="post" action="">
    <input value="<?echo $user?>" type="hidden" name="accountname_text">
    <table>
    <tr><td><textarea name="bulk_items" rows="10"  cols="50"></textarea></td></tr>
    </table>
    <input type="submit" name="submit_bulkset" value="Submit Bulk">
  </form>
<?
  $o = ob_get_contents();
  ob_end_clean();
  return $o;  
}

/**user gui for viewing predetermined user fields for current user only.
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */
function ewiki_page_uservars_user($id, $data, $action)
{
  global $ewiki_errmsg, $ewiki_user_gui_fields;
  
  $success=TRUE;
  
  //set user name to currently logged in user
  $user =$GLOBALS['ewiki_auth_user'];
  
  $o = ewiki_make_title($id, "User info for $user", 2); 
    
  //check to see if we are editing fields and that we have a username
  if(isset($_REQUEST['submit_changeaccount']) && !empty($user)){
    $editing=TRUE;
    foreach($_REQUEST as $request_key => $request_value){
      if(strstr($request_key, "text_")){ //only use the text_ inputs
        $varname=substr($request_key,5); //remove the text_ prefix
        if(!ewiki_set_uservar($varname, $request_value, $user)){ //set the appropriate info
          $success=FALSE; //something failed
        } 
      }
    }
    $o .= ($success ? ewiki_t("USERDATAUPDATESUCCESS") : $ewiki_errmsg);
  }
  
  $account_uservars=ewiki_getall_uservar($user); //get user info
  

  if(!empty($account_uservars)){ //if data exists for this user
    $o .= '<p><form method="post" action=""><table border="1" cellpadding="1">';
    foreach($account_uservars as $field => $value){ 
      if(isset($ewiki_user_gui_fields[$field])){ //check if this is a field we want the user to be able to edit 
        $o .= '<tr><th>'.$field.'</th><td><input name="text_'.$field.'" type="text" value="'.$value.'"></td></tr>';
      }
    }
    $o .= '</table>'.
      '<input value="Submit Changes" type="submit" name="submit_changeaccount" />'.
      '</form>';
  } else {
    $o .= ewiki_t("USERDATANODATA");
  }

  return $o;

}
?>