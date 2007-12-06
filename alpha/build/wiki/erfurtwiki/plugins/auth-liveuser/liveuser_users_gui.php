<?php

/**
 * Copyright (c) 2003, The Burgiss Group, LLC
 * This source code is part of eWiki LiveUser Plugin.
 *
 * eWiki LiveUser Plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * eWiki LiveUser Plugin is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Wiki LiveUser Plugin; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once(dirname(__FILE__).'/liveuser_aux.php');

/*
 * constant regulates whether subgroups are enabled. this option has been
 * disabled until LiveUser has complete support.
 */
define('LU_GUI_USER_SUBGROUPS', 0);

// ewiki callback for user/group administration page
$ewiki_plugins['page']['AdminUsers'] = 'ewiki_page_liveuser_admin_users';

/**
 * admin gui for modifying user accounts.
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */
function ewiki_page_liveuser_admin_users($id, $data, $action)
{
    global $liveuserAuthAdmin, $liveuserPermAdmin, $ewiki_plugins;

    ob_start();
    
    echo ewiki_make_title($id, $id, 2);    
  
    // handle posted deletes or updates
    if (isset($_POST['submit_deleteusers']) || isset($_POST['submit_changeusers']) || isset($_POST['submit_adduserstogroup']) ||
        isset($_POST['submit_removeusersfromgroup']) || isset($_POST['submit_changegroups'])) {
        foreach ($_POST as $key => $value) {
          
            list($prefix, $id) = explode('_', $key, 2);
            
            //get password status of current $id 
            $username=$_POST['origname_'.$id];
            $pwdstatus = ewiki_get_uservar("passwdstatus", NULL, $username);
            
            // Remove a user
            if ($prefix == 'chk' && is_numeric($id) && $value == 'on' && isset($_POST['submit_deleteusers'])) {
                if (liveuser_removeEntity('user_id', $id)) {
                    echo '<p>User '.$id.' was successfully removed.</p>';
                } else {
                    echo '<p>Removal of user '.$id.' failed.</p>';
                }
            }
            
            // Add a user to a group
            if ($prefix == 'chk' && is_numeric($id) && $value == 'on' && isset($_POST['submit_adduserstogroup'])) {
                if (($group_id = liveuser_checkEntity('group', $_POST['grouplist'])) !== false) {
                    if (liveuser_checkGroupUser($group_id, $id) === false) {
                        if ($liveuserPermAdmin->addUserToGroup($id, $group_id)) {
                            echo '<p>User '.$id.' was successfully added to group '.$_POST['grouplist'].'.</p>';
                        } else {
                            echo '<p>Addition of user '.$id.' to group '.$_POST['grouplist'].' failed.</p>';
                        }
                    } else {
                        echo '<p>User '.$id.' is already a member of group '.$_POST['grouplist'].'.</p>';
                    }
                } else {
                    echo '<p>Group '.$_POST['grouplist'].' does not exist.</p>';
                }
            }    
            
            // Remove a user from a group
            if ($prefix == 'chk' && is_numeric($id) && $value == 'on' && isset($_POST['submit_removeusersfromgroup'])) {
                if (($group_id = liveuser_checkEntity('group', $_POST['grouplist'])) !== false) {
                    if ($liveuserPermAdmin->removeUserFromGroup($id, liveuser_checkEntity('group', $_POST['grouplist']))) {
                        echo '<p>User '.$id.' was successfully removed from group '.$_POST['grouplist'].'.</p>';	
                    } else {
                        echo '<p>Removal of user '.$id.' from group '.$_POST['grouplist'].' failed.</p>';
                    }
                } else {
                    echo '<p>Group '.$_POST['grouplist'].' does not exist.</p>';
                }
            }
            
            // Change the user name
            if ($prefix == 'chname' && is_numeric($id) && !empty($value) && ($_POST['origname_'.$id] != $value) && isset($_POST['submit_changeusers'])) {	    
              $event_log='';
                if (liveuser_checkEntity('user', $value) === false) {
                    if ($liveuserAuthAdmin->updateUser($id, $value)) {	    
                    $event_log.='<p>User '.$value.' was successfully updated.</p>';
                    if(isset($ewiki_plugins['uservars_store'][0])){
                      if($ewiki_plugins['uservars_store'][0]($ewiki_plugins['uservars_retrieve'][0]($_POST['origname_'.$id]),$value)){
                        $event_log.='<p>User data copied to '.$value;
                        if($ewiki_plugins['uservars_store'][0](array(),$_POST['origname_'.$id])){
                          $event_log.= ' and deleted from '.$_POST['origname_'.$id];
                    } else {
                          $event_log.= ' but not deleted from '.$_POST['origname_'.$id];                          
                    }
                        $event_log.= '.</p>';                          
                } else {
                        $event_log.= '<p>User data copy failed.</p>';                        
                }	
                    }
                  } else {
                    $event_log.= '<p>Update of user '.$value.' failed.</p>';
                  }
              } else {
                  $event_log.= '<p>Another user with the name '.$value.' already exists in the database. No change has been made.</p>';
              }	
              echo($event_log);
              ewiki_log("Attempted to rename ".$_POST['origname_'.$id]." to $value.".$event_log,1);
            }
            
            // Change user variable
            if ($prefix == 'chuvar' && is_numeric($id) && !empty($value) && ($_POST['origchuvar_'.$id] != $value) && isset($_POST['submit_changeusers'])) {	 
              if(ewiki_set_uservar($_POST['uvar_fieldname'], $value, $username)){
                echo "<p>UserVar ".$_POST['uvar_fieldname']." successfully updated for $username</p>";
              } else {
                echo "<p>Update of UserVar ".$_POST['uvar_fieldname']." for $username failed.</p>";
              }
            }
            
            if ($prefix == "radpw" && is_numeric($id) && !empty($value) && isset($_POST['submit_changeusers'])) {
              if ($value=="expire" && ($pwdstatus=='good' || is_null($pwdstatus))){
                ewiki_set_uservar("passwdexpiredate", time(),$username);
                ewiki_set_uservar("passwdstatus", 'expired', $username);
              } elseif($value=="good" && ($pwdstatus=='expired' || is_null($pwdstatus))){
                ewiki_set_uservar("passwdexpiredate", time()+(60*60*24*EWIKI_PASSWD_LIFETIME),$username);
                ewiki_set_uservar("passwdstatus", 'good', $username);
              }
            }
            
            if ($prefix == 'chkrandpw' && is_numeric($id) && !empty($value) && isset($_POST['submit_changeusers'])) {
                $password=liveuser_generate_password();
                
                if ($liveuserAuthAdmin->updateUser($id, $_POST['chname_'.$id], $password)) {
                    ewiki_set_uservar("passwdexpiredate", time()-(60*60*24*EWIKI_PASSWD_LIFETIME),$username);
                    ewiki_set_uservar("passwdstatus", 'expired', $username);
                    echo '<p>Password for user '.$_POST['chname_'.$id]." was successfully updated to \"$password\" and set to expire in ".EWIKI_PASSWD_LIFETIME."days.</p>";
                } else {
                    echo '<p>Update of password for user '.$_POST['chname_'.$id].' failed.</p>';
                }
            }
            
            // Change the user's password
            if ($prefix == 'chpw' && is_numeric($id) && !empty($value) && isset($_POST['submit_changeusers'])) {
                // check for cracklib functions and validate against them if possible
                liveuser_admin_users_cracklib_check($_POST['chname_'.$id], $value);
                
                if ($liveuserAuthAdmin->updateUser($id, $_POST['chname_'.$id], $value)) {
                    ewiki_set_uservar("passwdexpiredate", time()-(60*60*24*EWIKI_PASSWD_LIFETIME),$username);
                    ewiki_set_uservar("passwdstatus", 'expired', $username);
                    echo '<p>Password for user '.$_POST['chname_'.$id].' was successfully updated and set to expire in '.EWIKI_PASSWD_LIFETIME.'days.</p>';
                } else {
                    echo '<p>Update of password for user '.$_POST['chname_'.$id].' failed.</p>';
                }
            }
            
            // Remove a group
            if ($prefix == 'chkgroup' && is_numeric($id) && $value == 'on' && isset($_POST['submit_changegroups'])) {
                if (liveuser_removeEntity('group_id', $id)) {
                    echo '<p>Group '.$id.' was successfully deleted.</p>';
                } else {
                    echo '<p>Deletion of group '.$id.' failed.</p>';
                }
            }
            
            // Change group name
            if ($prefix == 'chgroupname' && is_numeric($id) && !empty($value) && ($_POST['origgroupname_'.$id] != $value) && isset($_POST['submit_changegroups'])) {    
                if ($liveuserPermAdmin->updateGroup($id, $value)) {	    
                    echo '<p>Group '.$value.' was successfully updated.</p>';
                }  else {
                    echo 'Update of group '.$value.' failed.</p>';
                }	
            }
        }
    }
    
    // Add a user
    if (!empty($_POST['username_text']) && !empty($_POST['pw_text']) && isset($_POST['submit_adduser'])) {
	if (liveuser_checkEntity('user', $_POST['username_text']) === false) {
            // check for cracklib functions and validate against them if possible
            liveuser_admin_users_cracklib_check($_POST['chname_'.$id], $value);
            
            if (liveuser_addEntity('user', array($_POST['username_text'], $_POST['pw_text'])) !== false) {                
                echo '<p>User '.$_POST['username_text'].' was successfully created.</p>';    
	    } else {
                echo '<p>Creation of user '.$_POST['username_text'].' failed.</p>';
            }
        } else {
            echo '<p>User '.$_POST['username_text'].' already exists.</p>';
        }	
    }
    
    // Add a lot of users and add them into groups
    if (!empty($_POST['usernames_text']) && isset($_POST['submit_addusers'])) {    
	$newusers = explode("\n", $_POST['usernames_text']);
	
	foreach ($newusers as $newuser) {
	    $newuser = trim($newuser);
    	    	    
	    if (($auth_id = liveuser_checkEntity('user', $newuser)) === false) {	    
        if ($_POST["pwgen_addusers"]=="on"){
            $password=liveuser_generate_password();
        }else{
            $password=$newuser;
        }
		if (($auth_id = liveuser_addEntity('user', array($newuser, $password))) !== false) {
		    echo "<p>User $newuser was successfully created with password $password.</p>";
		} else {
                    echo '<p>Creation of user '.$newuser.' failed.</p>';
                }
	    } else {
		echo '<p>User '.$newuser.' already exists.</p>';
            }
		
            if ($auth_id !== false && !empty($_POST['usernames_grouplist'])) {
                if (($group_id = liveuser_checkEntity('group', $_POST['usernames_grouplist'])) !== false) {
                    if (liveuser_checkGroupUser($group_id, $auth_id) === false) {
                        if ($liveuserPermAdmin->addUserToGroup($auth_id, $group_id)) {
                            echo '<p>User '.$newuser.' was successfully added to group '.$_POST['usernames_grouplist'].'.</p>';
                        } else {
                            echo '<p>Addition of user '.$newuser.' to group '.$_POST['usernames_grouplist'].' failed.</p>';
                        }
                    } else {
                        echo '<p>User '.$newuser.' is already a member of group '.$_POST['usernames_grouplist'].'.</p>';
                    }
                } else {
                    echo '<p>Group '.$_POST['usernames_grouplist'].' does not exist.</p>';
                }
            }
	}    
    }

    // Add a group
    if (!empty($_POST['groupname_text']) && isset($_POST['submit_addgroup'])) {
        $group_id = liveuser_checkEntity('group', $_POST['groupname_text']);
        
        if ($group_id === false) {
            $group_const = 'LU_G_'.strtoupper($_POST['groupname_text']);
	    $group_id = liveuser_addEntity('group', array($group_const, $_POST['groupname_text'], null, true));
            
	    if ($group_id !== false) {
                echo '<p>Group '.$_POST['groupname_text'].' was successfully created.</p>'; 
            } else {
		echo '<p>Creation of group '.$_POST['groupname_text'].' failed.</p>';
	    }
        } else {
            echo '<p>Group '.$_POST['groupname_text'].' already exists.</p>';
        }

        if (isset($_POST['addright']) && $group_id !== false) { 
            $right_id = liveuser_checkEntity('right', $_POST['groupname_text']);
            
            if ($right_id === false) {
                $right_const = 'LU_R_'.strtoupper($_POST['groupname_text']);
                $right_id = liveuser_addEntity('right', array(LU_AREA_LIVEWEB, $right_const, $_POST['groupname_text']));
                
                if ($right_id !== false) {
                    echo '<p>Right '.$_POST['groupname_text'].' was successfully created.</p>';
                } else {
                    echo '<p>Creation of right '.$_POST['groupname_text'].' failed.</p>';
                }
            } else {
                echo '<p>Right '.$_POST['groupname_text'].' already exists.</p>';
            }
            
            if ($right_id !== false) {
                // check if group already has the right
                if (liveuser_checkGroupRight($group_id, $right_id)) {
                    echo 'Group '.$_POST['groupname_text'].' already has right '.$_POST['groupname_text'].'.</p>';
                } else {
                    // attempt to assign right to group
                    if ($liveuserPermAdmin->grantGroupRight($group_id, $right_id, 1) === true) {
                        echo '<p>Right '.$_POST['groupname_text'].' has been assigned to group '.$_POST['groupname_text'].'.</p>';
                    } else {
                        echo '<p>Assignment of right '.$_POST['groupname_text'].' to group '.$_POST['groupname_text'].' failed.</p>';
                    }
                }
            }
        }
    }
            
    // Show current table listing of pages and permissions
    $users = $liveuserAuthAdmin->getUsers();
    $groups = $liveuserPermAdmin->getGroups();
    
    
    //uservars based controls
    if(isset($ewiki_plugins['uservars_search'][0])){
      if(isset($_REQUEST['search_fieldname'])){ //set fieldname variable
        $fieldname=$_REQUEST['search_fieldname'];
      }
      if(strlen($_REQUEST['search_fieldvalue'])){ //set fieldvalue variable
        $fieldvalue=$_REQUEST['search_fieldvalue'];
      }
      if(!empty($fieldname)){
        $userdata = ewiki_search_uservar($fieldname, $fieldvalue); //get data for the given fieldname/fieldvalue combination
      
        //Remove non-matching users
        foreach ($users as $key=>$user) {
          if(!isset($userdata[$user['handle']])){
            unset($users[$key]);
          }
        }
      }

      //Display search form
      ?>
      <form method="post" action="">
        <table>
          <tr><td>Field Name</td><td>
			<input type="text" name="search_fieldname" value="<?=$fieldname?>">
			</td></tr>
          <tr><td>Value</td><td>
			<input type="text" name="search_fieldvalue" value="<?=$fieldvalue?>">
			</td></tr>
        </table>
        <input value="Search" type="submit" name="submit_searchaccount" />
      </form>
      
      <?
    }
    
    
    if (is_array($users) && !empty($users)) {
	?>
	    <form method="post" action="">
	    <h3>Edit Users</h3>
      <input type="hidden" name="uvar_fieldname" value="<?=$fieldname?>">
	    <table border="1">
	    <tr><th>Select</th><th>User ID</th><th>User Name<br />Password [Random]</th><th>Password Status</th><th>Groups</th>
        <?php
  if(!empty($fieldname)){?>

    <th><?=$fieldname?> 			
		<input type="hidden" name="search_fieldname" value="<?=$fieldname?>">
		<input type="hidden" name="search_fieldvalue" value="<?=$fieldvalue?>">
		</th>
	<?php
  }  
  echo('</tr>');
	foreach ($users as $user) {
	    ?>
                <tr>
                    <td><input name="chk_<?=$user['auth_user_id']?>" type="checkbox" /></td>
                    <td><?=$user['auth_user_id']?></td>
                    <td>
                        <input id="chname_<?=$user['auth_user_id']?>" name="chname_<?=$user['auth_user_id']?>" type="text" value="<?=$user['handle']?>" />
                        <input name="origname_<?=$user['auth_user_id']?>" type="hidden" value="<?=$user['handle']?>"><br />
                        
                        <input id="chpw_<?=$user['auth_user_id']?>" name="chpw_<?=$user['auth_user_id']?>" type="text" value="" />
                        <input name="chkrandpw_<?=$user['auth_user_id']?>" type="checkbox" />
                        <?=($liveuserAuthAdmin->encryptPW($user['handle']) == $user['passwd'] ? '<div class="warning">Password == User Name</div>' : '')?>                        
                    </td>
                    <td>
                    <?
                    $good='<input type="radio" name="radpw_'.$user["auth_user_id"].'" value="good" CHECKED >Good<br />'.
                            '<input type="radio" name="radpw_'.$user["auth_user_id"].'" value="expire">Expired<br />';
                    $expired='<input type="radio" name="radpw_'.$user["auth_user_id"].'" value="good">Good<br />'.
                            '<input type="radio" name="radpw_'.$user["auth_user_id"].'" value="expire" CHECKED >Expired<br />';
                    echo (ewiki_get_uservar("passwdstatus", 'good', $user['handle'])=='good' ? $good : $expired);
                    echo intval((ewiki_get_uservar("passwdexpiredate", time(), $user['handle'])-time())/(60*60*24))." Days<br />";
                    ?>
                    </td>
                    <td>
            <?php
	    
	    foreach ($liveuserPermAdmin->getGroups(array('where_user_id' => $user['auth_user_id'])) as $group) {
		echo $group['name'].'<br />';
	    }
	    
	    ?>
                    </td>
                
            <?php
    if(isset($userdata[$user['handle']])){
      echo ('<input id="origchuvar_'.$user['auth_user_id'].'" name="origchuvar_'.$user['auth_user_id'].'" type="hidden" value="'.$userdata[$user['handle']].'">');
      echo('<td> <input id="chuvar_'.$user['auth_user_id'].'" name="chuvar_'.$user['auth_user_id'].'" type="text" value="'.$userdata[$user['handle']].'" /></td>');
    }
    echo("</tr>");
	}    
	
	?>
            </table>
            <input type="reset" value="Reset" />
            <input type="submit" name="submit_deleteusers" value="Delete Selected" />
            <input type="submit" name="submit_changeusers" value="Submit Changes" />
        <?php
        
        if (is_array($groups) && !empty($groups)) {
            ?>
                <br /><br /><label for="grouplist">Group</label>
                <select id="grouplist" name="grouplist">
            <?php
	    
            foreach ($groups as $group) {
                echo '<option value="'.$group['name'].'">'.$group['name'].'</option>';
            }
	    
            ?>
                </select><br />
                <input type="submit" name="submit_adduserstogroup" value="Add Selected" />
                <input type="submit" name="submit_removeusersfromgroup" value="Remove Selected" />
            <?php
        }
        
        echo '</form>';
    } else {
	?>
            <h3>Edit Users</h3>
            <p>No users were found in the database.</p>
        <?php
    }   
    
    // Show Add a new user section
    ?>
	<form method="post" action="">
	<h3>Add a User</h3>
	<label for="username_text">User Name</label>
	<input id="username_text" name="username_text" type="text" /><br />
	<label for="pw_text">Password</label>
	<input id="pw_text" name="pw_text" type="text" /><br />
	<input type="submit" name="submit_adduser" value="Add User" />
	</form>
    <?php
	
    // Show Add multiple users section
    ?>
	<form method="post" action="">
	<h3>Add Multiple Users</h3>
        <p>Insert one user name per line. This input will be processed as a 
        batch, and each user will be created with a password identical to his 
        user name or a randomly generated password if the "Generate Passwords" 
        box is checked.</p>
	<textarea id="usernames_text" name="usernames_text" rows="10" cols="25"></textarea>    
    <?php
    
    if (is_array($groups) && !empty($groups)) {
        ?>
            <label for="usernames_grouplist">Groups</label>
            <select id="usernames_grouplist" name="usernames_grouplist" />
            <option value=""></option>
        <?php
	
        foreach ($groups as $group) {
            echo '<option value="'.$group['name'].'">'.$group['name'].'</option>';
        }
        
        ?>
            </select>
        <?php
    }
    
    ?>
        <p><input type="checkbox" name="pwgen_addusers" checked="checked"> Generate random passwords.</p>
        <input type="submit" name="submit_addusers" value="Add Users" />    
        </form>
    <?php
    
    // Groups Section
    if (is_array($groups) && !empty($groups)) {
	?>
	    <form method="post" action="">
	    <h3>Edit Groups</h3>
	    <table border="1">
	    <tr><th>Delete</th><th>Group ID</th><th>Group Name</th></tr>
        <?php
	
	foreach ($groups as $group) {	
	    ?>
                <tr>
                    <td><input name="chkgroup_<?=$group['group_id']?>" type="checkbox" /></td>
                    <td><?=$group['group_id']?></td>
                    <td>
                        <input name="chgroupname_<?=$group['group_id']?>" type="text" value="<?=$group['name']?>" />
                        <input name="origgroupname_<?=$group['group_id']?>" type="hidden" value="<?=$group['name']?>" />
                    </td>
                </tr>
            <?php
	}
        
        ?>
            </table>
            <input type="reset" value="Reset" />
            <input name="submit_changegroups" type="submit" value="Submit Changes">
            </form>
        <?php
    } else {
	?>
            <h3>Edit Groups</h3>
            <p>No groups were found in the database.</p>
        <?php
    }
    
    // Show Add a new group section
    ?>
	<form method="post" action="">
	<h3>Add a Group</h3>
        <p>When creating a group, you may choose to create a right with the group, which may then be applied to user accounts via the group. If the group already exists, this form will still attempt to link a right to it. If the right already exists and is not associated with the group, it will be assigned to the group.</p>
	<label for="groupname_text">Group Name</label>
	<input id="groupname_text" name="groupname_text" type="text"><br />
	<label for="addright">Add/Link Right</label>
	<input id="addright" name="addright" type="checkbox" checked="checked"><br />
	<input type="submit" name="submit_addgroup" value="Add Group" />
	</form>
    <?php
	
    $o = ob_get_contents();
    ob_end_clean();
    return $o;     
}

/**
 * checks password with cracklib and outputs warning message if insecure.
 *
 * @param string username
 * @param string password
 */
function liveuser_admin_users_cracklib_check($username, $password)
{
    if (extension_loaded('crack') && function_exists('crack_check') && function_exists('crack_getlastmessage')) {
        crack_check($value);
        
        if (crack_getlastmessage() != "strong password") {
            echo '<p>Password for user '.$username.' is not secure, cracklib reports: '.crack_getlastmessage().'.</p>';
        }
    }
    
    //Jeff's password checker, copied from auth_liveuser.php
    $password_status=ewiki_check_passwd($password,$username);
    //$end=getmicrotime();
    //echo($end-$time);
    if ($password_status!='good passwd') {
      if($password_status=='read error'){
        echo ewiki_t('PASS_DICTIONARY_READ_ERROR');
      } else {
        echo ewiki_t($password_status);
      }
    }
}

?>