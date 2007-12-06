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
#require_once(dirname(__FILE__).'/pref_liveuser.php');

// ewiki callback for user/group administration page
$ewiki_plugins['page']['AdminAddUsers'] = 'ewiki_page_liveuser_admin_add_users';

/**
 * simple admin gui for adding user accounts.
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */ 
function ewiki_page_liveuser_admin_add_users($id, $data, $action)
{
    global $liveuserAuthAdmin, $liveuserPermAdmin;

    ob_start();
    
    echo ewiki_make_title($id, $id, 2);
    
    // add a user
    if (isset($_POST['submit_addusers'])) {
      if (empty($_POST['text_E-Mail_Address']) || empty($_POST['group_list'])) {
        echo '<p>Invalid form input was provided. Please ensure that the email and group fields are set and not empty.</p>';
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
      }
        
        
    if (strlen($_POST['text_E-Mail_Address']) > 255) {
      echo '<p>The email field input is too long. Please ensure that it is 255 characters or less.</p>';
      $o = ob_get_contents();
      ob_end_clean();
      return $o;     
    }
        
    if(!empty($_POST['username_text'])){
      $username=$_POST['username_text'];
    } else {
      $username=$_POST['text_E-Mail_Address'];
    }
        
        // ensure user does not already exist
    if (($auth_id = liveuser_checkEntity('user', $username)) === false) {
      
      $pwd=liveuser_generate_password();
      
      if (($auth_id = liveuser_addEntity('user', array($username, $pwd))) !== false) {
        echo '<p>User '.$username.' was inserted into the database.<br />The following password has been set for this user: '.$pwd.'</p>';
        //set preferences on successful creation                
        foreach($_POST as $post_key => $post_value){
          if(strstr($post_key, "text_")){ //only work on fields with the text_ prefix
            $varname=substr($post_key,5); //remove the text_ prefix
            if(!ewiki_set_uservar($varname, $post_value, $username)){ //set appropriate data
              echo '<p>An error occurred while setting the additional preferences for the user.</p>';
            }
          } 
        }                
        if (is_numeric($group_id = liveuser_checkEntity('group', $_POST['group_list'])) &&
        $liveuserPermAdmin->addUserToGroup($auth_id, $group_id) === true) {
        echo '<p>User '.$username.' was added into the group: '.$_POST['group_list'].'</p>';
        } else {
          echo '<p>An error occurred while adding the user to the group: '.$_POST['group_list'].'</p>';
        }
      } else {
        echo '<p>An error occurred while creating the user.</p>';
      }
    } else {
      echo '<p>User '.$username.' already exists and will not be created or modified.</p>';
    }	
  }  
  
    // show form
    ?>
	<form method="post" action="">
	<h3>Add a User</h3>
	<table>
  <tr><td><label for="email_text">E-Mail Address</label></td>
  <td><input name="text_E-Mail Address" type="text" maxlength="255" /><br /></td></tr>
  <tr><td><label for="username">User Name (if different from E-mail Address)</td>
  <td><input id="username_text" name="username_text" type="text" /></td></tr> 	
	<tr><td><label for="group_list">Group</label></td>
	<td><select id="group_list" name="group_list">
    <?php
	foreach ($liveuserPermAdmin->getGroups() as $group) {
	    echo '<option value="'.$group['name'].'">'.$group['name'].'</option>';
	}	 
    ?>
    </select></tr></td>
    <tr><td><label for="firstname_text">First Name</label></td>	
    <td><input name="text_First Name" type="text" /></td></tr>
    <tr><td><label for="middlename_text">Middle Name</label></td>	
    <td><input name="text_Middle Name" type="text" /></td></tr>
    <tr><td><label for="lastname_text">Last Name</label></td>	
    <td><input name="text_Last Name" type="text" /></td></tr>
    <tr><td><label for="title_text">Title</label></td>	
    <td><input name="text_Title" type="text" /></td></tr>
    <tr><td><label for="comp_text">Company</label></td>	
    <td><input name="text_Company" type="text" /></td></tr>
    <tr><td><label for="phone_text">Phone</label></td>
    <td><input name="text_Phone Number" type="text" /></td></tr>
    <tr><td><label for="addr_text">Address</label></td>
    <td><input name="text_Address"  type="text" /></td></tr>
    <tr><td><label for="city_text">City</label></td>
    <td><input name="text_City" type="text" /></td></tr>
    <tr><td><label for="state_text">State</label></td>
    <td><input name="text_State" type="text" /></td></tr>
    <tr><td><label for="zipcode_text">Zip Code</label></td>
    <td><input name="text_Zip Code"  type="text" /></td></tr>
    <tr><td><label for="country_text">Country</label></td>
    <td><input name="text_Country" type="text" /></td></tr>
  </table>
	<input type="submit" name="submit_addusers" />
	</form>
    <?php
    
    $o = ob_get_contents();
    ob_end_clean();
    return $o;     
}

?>