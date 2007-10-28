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

/*
 * note: this file should be included by all ewiki liveuser plugin scripts. it
 * defines a number of utility functions, instantiates required liveuser
 * objects used throughout the plugin, and ensures the inclusion of liveuser's
 * configuration.
 *
 * note: The following abstraction functions treat the name value of entities as
 * a unique id (although it does not have to be) along with the existing
 * numerical id (unique by default). For language entities, these functions will
 * return the 2-letter language code as the unique identifier.
 */

require_once('LiveUser/LiveUser.php');
require_once('LiveUser/Admin/Auth/Container/DB.php');
require_once('LiveUser/Admin/Perm/Container/DB_Complex.php');
require_once(dirname(__FILE__).'/liveuser_conf.php');


define('LEVENSTIEN_MIN',6);

/* fetch auth/perm admin objects */
$liveuserAuthAdmin = &new LiveUser_Admin_Auth_Container_DB($liveuserConfig['authContainers'][0]);
$liveuserPermAdmin = &new LiveUser_Admin_Perm_Container_DB_Complex($liveuserConfig['permContainer']);

/**
 * Translates a username or auth object user id into a perm object user id. The
 * argument type may either be 'user' or 'user_id', to denote if the second
 * id argument is a username or auth id.
 *
 * @param string type of second argument
 * @param mixed id integer auth_id or username
 * @return integer corresponding perm_id if user exists, false otherwise
 */
function liveuser_getPermUserId($type, $id)
{
    global $liveuserDB, $liveuserConfig;

    $container_name = $liveuserConfig['authContainers'][0]['name'];
    
    if (($type == 'user' || $type == 'user_id') &&
        ($auth_id = liveuser_checkEntity($type, $id)) !== false) {
        $perm_user_id = $liveuserDB->getOne('
            SELECT liveuser_perm_users.perm_user_id
            FROM liveuser_perm_users, liveuser_users
            WHERE liveuser_users.auth_user_id = ? AND
                liveuser_perm_users.auth_container_name = ? AND
                liveuser_users.auth_user_id = liveuser_perm_users.auth_user_id',
            array($auth_id, $container_name));
        return (is_numeric($perm_user_id) ? $perm_user_id : false);
    } else {
        return false;
    }
}

/**
 * Allows for duplicate-safe creation of LiveUser entities. The supplied entity
 * name must be one of the following: language, application; area; right; group;
 * or user. This function expects the following global variables:
 *
 *      $liveuserAuthAdmin (of type LiveUser_Admin_Auth_Container_DB)
 *      $liveuserPermAdmin (of type LiveUser_Admin_Perm_Container_DB_Complex)
 *      $liveuserConfig    (LiveUser configuration array)
 *
 * @param string type name of entity type to add
 * @param array args array corresponding to argument list of the entity's add method
 * @return mixed unique id (integer or string) of added entity on success, false on error
 */
function liveuser_addEntity($type, $args)
{
    global $liveuserAuthAdmin, $liveuserPermAdmin, $liveuserConfig;

    switch ($type) {
        // special case, add language, but return 2-letter code instead of numerical id
        case 'language':
            if (($languageId = liveuser_checkEntity($type, $args[0])) === false) {
                $languageId = call_user_func_array(array(&$liveuserPermAdmin,'addLanguage'), $args);
                // return 2-letter code for success, false otherwise
                return (is_numeric($languageId) ? $args[0] : false);
            }
            return $args[0];
            break;
        // special case, add user to both auth and perm containers
        case 'user':
            $perm_id = 0;
            if (($auth_id = liveuser_checkEntity($type, $args[0])) === false) {
                $auth_id = call_user_func_array(array(&$liveuserAuthAdmin,'addUser'), $args);
                $perm_id = $liveuserPermAdmin->addUser($auth_id, LIVEUSER_USER_TYPE_ID, null, $liveuserConfig['authContainers'][0]['name']);
            }
            return (is_numeric($auth_id) && is_numeric($perm_id) ? $auth_id : false);
            break;
        // common cases, fetch unique name from arguments and proceed
        case 'application':
            $argName = $args[1];
            break;
        case 'area':
            $argName = $args[2];
            break;
        case 'right':
            $argName = $args[2];
            break;
        case 'group':
           $argName = $args[1];
            break;
        // failure case, unknown type
        default:
            return false;
            break;
    }

    // common check and create cases where type is: application, area, right, group
    if (($entityId = liveuser_checkEntity($type, $argName)) === false) {
        $entityId = call_user_func_array(array(&$liveuserPermAdmin,'add'.$type), $args);
    }
    return (is_numeric($entityId) ? $entityId : false);
}

/**
 * Checks for existing LiveUser entities. The supplied entity name must be one
 * of the following: application; area; right; group; user; or language. These
 * types will denote that the id parameter is in the form of the entity name. To
 * denote that the id parameter is in the form of the numeric id, the suffix
 * '_id' should be appended to the type names listed above. Depending on the
 * supplied id parameter, this function will search for entities matching the
 * numerical id before matching against the string name. All name id values must
 * be non-numeric strings. This function expects the following global variables:
 *
 *      $liveuserAuthAdmin (of type LiveUser_Admin_Auth_Container_DB)
 *      $liveuserPermAdmin (of type LiveUser_Admin_Perm_Container_DB_Complex)
 *
 * @param string type name of entity type to add
 * @param mixed id integer id or unique name of entity to check for depending on type
 * @return mixed unique id (integer or string) of entity if it exists, false otherwise
 */
function liveuser_checkEntity($type, $id)
{
    global $liveuserDB, $liveuserConfig, $liveuserAuthAdmin, $liveuserPermAdmin;

    switch ($type) {
	// special case, check for existing language and handle 2-character name
        case 'language':
        case 'language_id':
            $languages = $liveuserPermAdmin->getLanguages();
            foreach ($languages as $code => $language) {
                if (($type == 'language' && $code == $id && strlen($id) == 2) ||
                    ($type == 'language_id' && $language['language_id'] == $id)) {
                    return $code;                    
                }
            }
            return false;
            break;	
            
        // special case, check against user entity sets in both auth and perm containers
        case 'user':
        case 'user_id':
            $users = $liveuserAuthAdmin->getUsers();
            $auth_id = null;
            foreach ($users as $user) {
                if (($type == 'user' && $user['handle'] == $id) ||
                    ($type == 'user_id' && $user['auth_user_id'] == $id)) {
                    $auth_id = $user['auth_user_id'];
                    break;
                }
            }
            // no match if user_id is unset, otherwise proceed to check with perm container
            if (!isset($auth_id)) {
                return false;
            } else if ($liveuserDB->getOne('SELECT 1 FROM '.$liveuserConfig['permContainer']['prefix'].'perm_users WHERE auth_user_id = ?', array((int)$auth_id)) == 1) {
                return $auth_id;
            } else {
                return false;
            }
            break;
	    
        // common cases, check against entity selection
        case 'application':
        case 'area':
        case 'right':
        case 'group':
            $entities = call_user_func_array(array(&$liveuserPermAdmin, 'get'.$type.'s'), array());
            foreach ($entities as $entity) {
                if ($entity['name'] == $id) {
                    return $entity[$type.'_id'];
                }
            }
	    return false;
	    break;
	    
	case 'application_id':
	case 'area_id':
	case 'right_id':
	case 'group_id':
            $entities = call_user_func_array(array(&$liveuserPermAdmin, 'get'.substr($type, 0, -3).'s'), array());
            foreach ($entities as $entity) {
                if ($entity[$type] == $id) {
                    return $entity[$type];
                }
            }
	    return false;
	    break;
	    
        // failure case, unknown type or no match for entity
        default:
            return false;
            break;
    }
}

/**
 * Allows for removal of LiveUser entities. The supplied entity name must be one
 * of the following: language, application; area; right; group; or user. These
 * types imply that the id parameter is in the form of the entity name. To
 * denote the id parameter as an entity numeric id, the suffix '_id' should be
 * appended to the names. This function expects the following global variables:
 *
 *      $liveuserAuthAdmin (of type LiveUser_Admin_Auth_Container_DB)
 *      $liveuserPermAdmin (of type LiveUser_Admin_Perm_Container_DB_Complex)
 *
 * @param string type name of entity type to remove
 * @param mixed id integer id or unique name of entity to remove depending on type
 * @return true if the entity existed and was removed, false otherwise
 */
function liveuser_removeEntity($type, $id)
{
    global $liveuserDB, $liveuserConfig, $liveuserAuthAdmin, $liveuserPermAdmin;

    switch ($type) {
        // special case, remove user to both auth and perm containers
        case 'user':
	case 'user_id':
            // log removed users with ewiki_log
            if (($auth_id = liveuser_checkEntity($type, $id)) !== false &&
                ($perm_id = liveuser_getPermUserId('user_id', $auth_id)) !== false) {
                    // fetch authTable names
                    $authTable = $liveuserConfig['authContainers'][0]['authTable'];
                    
                    // backup user preferences and groups of user being removed
                    $backup['prefs'] = $liveuserDB->getAll('SELECT '.LW_PREFIX.'_prefs_fields.field_name, '.LW_PREFIX.'_prefs_data.field_value
                        FROM '.LW_PREFIX.'_prefs_fields, '.LW_PREFIX.'_prefs_data
                        WHERE '.LW_PREFIX.'_prefs_data.user_id = ? AND '.LW_PREFIX.'_prefs_data.field_id = '.LW_PREFIX.'_prefs_fields.field_id',
                        array((int)$perm_id));
                    
                    // direct sql required to fetch group_define_name
                    $backup['groups'] = $liveuserDB->getAll('SELECT liveuser_groups.group_define_name
                        FROM liveuser_groups, liveuser_groupusers
                        WHERE liveuser_groupusers.perm_user_id = ? AND liveuser_groupusers.group_id = liveuser_groups.group_id',
                        array((int)$perm_id));
                    
                    // output serialized data to log file                    
                    $handle = $liveuserDB->getOne('SELECT handle FROM ! WHERE auth_user_id = ?', 
                        array($authTable, (int)$auth_id));                    
                    ewiki_log('liveuser: removed user: '.$handle.'|'.serialize($backup), 1);                    		
                
                // remove records of user in auth/perm containers and the user's preferences
                if ($liveuserPermAdmin->removeUser($perm_id) !== true) {
                    return false;
                }                
                if ($liveuserAuthAdmin->removeUser($auth_id) !== true) {
                    return false;
                }
                return ($liveuserDB->query('DELETE FROM '.LW_PREFIX.'_prefs_data WHERE '.LW_PREFIX.'_prefs_data.user_id = ?',
                    array((int)$perm_id)) == DB_OK);
            }
            return false;
            break;
	    
        // common cases, fetch unique name from arguments and proceed
        case 'language':
        case 'application':
        case 'area':
        case 'right':
        case 'group':
            if (($entityId = liveuser_checkEntity($type, $id)) !== false) {
                if (call_user_func_array(array(&$liveuserPermAdmin,'remove'.$type), $entityId) !== true) {
                    return false;
                }
                return true;
            }
	    return false;
	    break;

	case 'language_id':
	case 'application_id':
	case 'area_id':
	case 'right_id':
	case 'group_id':
            if (($entityId = liveuser_checkEntity($type, $id)) !== false) {
                if (call_user_func_array(array(&$liveuserPermAdmin,'remove'.substr($type, 0, -3)), $entityId) !== true) {
                    return false;
                }
                return true;
            }
	    return false;
	    break;

        // failure case, unknown type or no match for entity
        default:
            return false;
            break;
    }   
}

/**
 * Allows for duplicate-safe creation of page permissions. If the permissions
 * already exists, the page/right record will be updated, rather than inserted.
 * Permissions may be specified as a combination of the page name and right_id,
 * or the integer id of the permission may be supplied as the first parameter
 * and the right_id parameter omitted. The preserveHigher parameter may be set
 * to ensure that existing permissions higher than the permission being set
 * are not overwritten, thus making the update have no effect.
 *
 * @param string type name of entity type to remove
 * @param mixed page_id ewiki page name as a string or integer id
 * @param int ring ewiki ring level
 * @param int right_id LiveUser right id
 * @param boolean preserveHigher does not overwrite higher permissions if true
 *
 * @return boolean true if permission was successfully added or modified, false otherwise
 */
function liveuser_addPerm($page_id, $ring, $right_id = null, $preserveHigher = false)
{
    global $liveuserDB;

    if (($perm_id = liveuser_checkPerm($page_id, $right_id)) !== false) {
        if ($preserveHigher) {
            return ($liveuserDB->query('UPDATE '.LW_PREFIX.'_perms SET ring = ? WHERE id = ? AND ring > ?',
                array((int)$ring, (int)$perm_id, (int)$ring)) == DB_OK);
        } else {
            return ($liveuserDB->query('UPDATE '.LW_PREFIX.'_perms SET ring = ? WHERE id = ?',
                array((int)$ring, (int)$perm_id)) == DB_OK);
        }
    } else {
        return ($liveuserDB->query('INSERT INTO '.LW_PREFIX.'_perms (pagename, ring, right_id) VALUES (?, ?, ?)',
            array($page_id, (int)$ring, (int)$right_id)) == DB_OK);
    }
    return false;
}

/**
 * Checks if a page permission already exists in the database. Permissions
 * may be specified as a combination of the page name and right_id, or the
 * integer id of the permission may be supplied as the first and only parameter.
 *
 * @param mixed page_id ewiki page name as a string or integer id

 * @param int right_id LiveUser right id
 *
 * @return mixed integer identifier if permission exists, false otherwise
 */
function liveuser_checkPerm($page_id, $right_id = null)
{
    global $liveuserDB;

    $perm_id = null;

    if (is_null($right_id)) {
        if (is_numeric($page_id)) {
            $perm_id = $liveuserDB->getOne('SELECT id FROM '.LW_PREFIX.'_perms WHERE id = ?',
                array($page_id));
        } else {
            // non-numeric page_id with null right_id, invalid input
            return false;
        }       
    } else {
        $perm_id = $liveuserDB->getOne('SELECT id FROM '.LW_PREFIX.'_perms WHERE pagename = ? AND right_id = ?',
            array($page_id, (int)$right_id));
    }
    return (is_numeric($perm_id) ? $perm_id : false);
}

/**
 * Allows for removal of page permissions. Permissions may be specified as a
 * combination of the page name and right_id, or the integer id of the permission
 * may be supplied as the first and only parameter. The ring_min parameter may
 * be used to limit the permissions that will be removed by ensuring that their
 * ring level is at least the value given. The default value (0: highest access
 * level) would allow all permissions to be removed.
 *
 * @param mixed page_id ewiki page name as a string or integer id
 * @param int right_id LiveUser right id
 * @param int ring_min remove only if ring level is >= this value
 *
 * @return boolean true if permission was successfully removed, false otherwise
 */
function liveuser_removePerm($page_id, $right_id = null, $ring_min = 0)
{
    global $liveuserDB;

    if (($perm_id = liveuser_checkPerm($page_id, $right_id)) !== false) {
        return ($liveuserDB->query('DELETE FROM '.LW_PREFIX.'_perms WHERE id = ? AND ring >= ?',
            array((int)$perm_id, (int)$ring_min)) == DB_OK);
    }
    return false;
}

/**
 * Retrieves all permissions for a page. Permissions may be specified as a
 * combination of the pagename and right_id, or the integer id of the permission
 * may be supplied as the first and only parameter.
 *
 * @param string pagename ewiki page name as a string
 * @param int ring_min retrieve only if ring level is >= this value
 *
 * @return mixed array of permissions for the page
 */
function liveuser_getPerms($pagename, $ring_min = 0)
{
    global $liveuserDB;

    return $liveuserDB->getAll('SELECT * FROM '.LW_PREFIX.'_perms WHERE pagename = ? AND ring >= ?',
        array($pagename, (int)$ring_min));
}

/**
 * Checks if a group has a right.
 *
 * @param integer group_id group_id
 * @param integer right_id right id
 * @return boolean true if group has the right, false otherwise
 */
function liveuser_checkGroupRight($group_id, $right_id)
{
    global $liveuserPermAdmin;

    $groups = $liveuserPermAdmin->getGroups(array('where_group_id' => $group_id, 'with_rights' => true));

    if (!is_array($groups)) {
        return false;
    }
    
    // result of getGroups() is an array [result set] of arrays [groups]
    foreach ($groups as $group) {
        $rights = $group['rights'];
        foreach($rights as $right) {
            if ($right['right_id'] == $right_id) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Checks if a user is a member of a group.
 *
 * @param integer auth_id auth_id
 * @param integer group_id group_id
 * @return boolean true if user is in the group, false otherwise
 */
function liveuser_checkGroupUser($group_id, $auth_id)
{
    global $liveuserPermAdmin;

    if (is_numeric($auth_id) && is_numeric($group_id) &&
        is_numeric($perm_id = liveuser_getPermUserId('user_id', $auth_id))) {
        $groups = $liveuserPermAdmin->getGroups(array('where_user_id' => $perm_id, 'where_group_id' => $group_id));
        return (is_array($groups) && !empty($groups));
    }
    return false;
}
/* Generates passwords for liveuser_gui and liveuser_addusers_gui*/
function liveuser_generate_password(){
    $pwd = '';  // to store generated password
    $len = rand(LW_PASSWORD_LEN_MIN, LW_PASSWORD_LEN_MAX); // password length
      
    do{  
      // generate random number sequence of ascii characters
      for ($i = 0; $i < $len; $i++) {
          $num = rand(48, 122);
          
          if ($num >= ord('a') && $num <= ord('z')) {   
              $pwd .= chr($num);		    
          } else if ($num >= ord('A') && $num <= ord('Z')) {
              $pwd .= chr($num);
          } else if ($num >= ord('0') && $num <= ord('9')) {
              $pwd .= chr($num);
          } else if ($num >= ord('#') && $num <= ord('&')) {
              $pwd .= chr($num);
          } else if ($num >= ord('?') && $num <= ord('@')) {
              $pwd .= chr($num);		
          } else {
              $i--;
          }
      }
    }while(ewiki_check_passwd($pwd)!="good passwd");

    return($pwd);
}

function ewiki_calc_complexity($passlen, $groups){
  //checks for password complexity
  return intval(log10(pow($groups,$passlen))/log10(2));
}

/*Checks to see if the password meets or exceeds a given complexity. 
  Checks to see if the password is based off of the user name.
  Checks to see if the password is or contains a dictionary word.
*/
function ewiki_check_passwd(&$password, $username=NULL, $skipdict=0){
  $lcase_password=strtolower($password);
  $username=strtolower($username);
  $possibilities=0;
  $a_complexity['[a-z]']=26; //lowercase letters
  $a_complexity['[A-Z]']=26; //uppercase letters
  $a_complexity['[0-9]']=10; //numbers
  $a_complexity['[()<>[\]{}!-.?,\'";:]']=17; //punctuation
  $a_complexity['[@#$%^&*_=+\\/|`~ ]']=16; //misc symbols

  $passlen=strlen($password);
  //determine the what sets are contained in the password
  foreach($a_complexity as $regex => $count){
    //echo("checking $regex ");
    if(preg_match("/".$regex."/", $password)){
      //echo("found $regex ");
      $possibilities+=$count;
    }
  }
  
  $complexity=ewiki_calc_complexity($passlen, $possibilities);
  if($complexity<EWIKI_PASSWORD_COMPLEXITY){
    return("CHPW_BADNEW_COMPLEXITY");
  }

  if(strlen($username)){  
    //checks to see if the password is based on the user name
    $dist = levenshtein($username, $lcase_password); //check forward
    $rdist = levenshtein(strrev($username), $lcase_password); //check backward
    if($dist<=LEVENSTIEN_MIN || strstr($lcase_password, $username) || strstr($lcase_password, strrev($username)) || $rdist<=LEVENSTIEN_MIN){
      return("CHPW_BADNEW_USERNAME");
    }
  }
  
  if(!$skipdict&&EWIKI_PASS_DICT_PATH){
    //gets dictionary and dumps it into a string (fastest way to read a large file)
    $dictionary=file_get_contents(EWIKI_PASS_DICT_PATH, 1);
    
    //checks that the file was read properly
    if(!$dictionary){
      return("read error");
    }
    
    $chunk_length=EWIKI_MIN_DICT_WORD_LENGTH;

    do{ //length of chunk
    $position=0;
    $chunks=$passlen-$chunk_length;
      do{ //position of beginning of chunk
        $subword=substr($password, $position, $chunk_length);
        if(strpos($dictionary, "\n".strtolower($subword)."\n")){
          $wordsfound[$subword]=strlen($subword);
        }
      } while(++$position<=$chunks);
    } while(++$chunk_length<=$passlen);
    
    unset($dictionary); //the dictionary is huge, no need to keep it anymore
       
    if(isset($wordsfound)){
      
      arsort($wordsfound);
    
      foreach($wordsfound as $word => $cred){
        //echo("found $word ");
        $password = str_replace($word, $cred, $password);
      }
      //echo(" calculating complexity of $password");
      if(ewiki_calc_complexity(strlen($password),$possibilities)<EWIKI_PASSWORD_COMPLEXITY){
        return ('CHPW_BADNEW_DICTIONARY');
      }
    }
  }

  //the password passed all the checks...its good enough...for now
  return("good passwd");
}



// initializes default language to 'EN'
$liveuserPermAdmin->setCurrentLanguage(liveuser_addEntity('language', array('EN', 'English', 'English')));

?>