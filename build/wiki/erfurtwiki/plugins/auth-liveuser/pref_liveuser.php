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

/**
 * ewiki: preferences api for liveuser plugin
 *
 * @author andy fundinger <afundinger@burgiss.com>
 * @author alex wan <alex@burgiss.com>
 * @author jeremy mikola <jmikola@arsjerm.net>
 *
 * This interface provides functions to save and retrieve user preferences, as
 * well as to configure available preference fields.
 */

require_once(dirname(__FILE__).'/auth_liveuser.php');
require_once(dirname(__FILE__).'/liveuser_aux.php');

$ewiki_plugins['page']['ChangePrefs'] = 'ewiki_page_liveuser_chprefs';

/**
 * changes user preferences based on form input
 *
 * @param mixed id
 * @param mixed data
 * @return mixed
 */
function ewiki_page_liveuser_chprefs($id, $data)
{ 
    global $liveuser, $liveuserDB;
    
    // if form was submitted, write 
    if (isset($_REQUEST['submit_prefs'])) {
        return ewiki_t('CHPWFORM');
    }
        
    ob_start();
    
    echo ewiki_make_title($id, $id, 2);
    
    $results = $liveuserDB->getAll('SELECT * FROM '.LW_PREFIX.'_prefs_fields');
    
    foreach ($results as $result) {
	if (isset($_REQUEST[$result['field_name']])) {
	    liveuser_pref_setPref($liveuser->getHandle(), $result['field_name'], $_REQUEST[$result['field_name']]);
	}
    }

    echo '<form action="" method="post"><table border="1">';	
    
    foreach ($results as $result) {
	echo '<tr><td>';
	echo '<label for="'.$result['field_name'].'">'.$result['field_name'].'</label></td><td>';
	echo '<input id="'.$result['field_name'].'" name="'.$result['field_name'].'" type="text" ';
	echo ' value='.liveuser_pref_getPref($liveuser->getHandle(), $result['field_name']).'></td></tr>';	
    }   
    
    echo '<tr><td colspan="2"><input type="reset" text="Reset"><input type="submit"></td></tr></table></form>';        
    
    $o = ob_get_contents();
    ob_end_clean();
    
    return $o;
}

/**
 * Checks if the specified preference already exists for the specified user.
 *
 * @param string username user handle 
 * @param string field_name preference field name
 * @return boolean true if preference exists, false otherwise
 */
function liveuser_pref_checkPref($username, $field_name)
{
    global $liveuserDB;
    
    $user_id = liveuser_getPermUserId($username);
    $field_id = $liveuserDB->getOne('SELECT field_id FROM '.LW_PREFIX.'_prefs_fields WHERE field_name = ?',
            array($field_name));
    
    return (is_numeric($field_id) ? $field_id : false);
}

/**
 * Fetches the specified preference's value for the specified user.
 *
 * @param string username user handle
 * @param string field_name preference field name
 * @param boolean useDefault if true, default field value is supplied for non-existent pref, else null is used
 * @return string preference field value if set, default value or null otherwise
 */
function liveuser_pref_getPref($username, $field_name, $useDefault = true)
{
    global $liveuserDB;
    
    $user_id = liveuser_getPermUserId($username);
    $field_value = $liveuserDB->getOne('
	SELECT '.LW_PREFIX.'_prefs_data.field_value 
	FROM '.LW_PREFIX.'_prefs_data, '.LW_PREFIX.'_prefs_fields 
	WHERE '.LW_PREFIX.'_prefs_data.user_id = ? 
	AND '.LW_PREFIX.'_prefs_data.field_id = '.LW_PREFIX.'_prefs_fields.field_id 
	AND '.LW_PREFIX.'_prefs_fields.field_name = ?',
        array((int)$user_id, $field_name));
    
    if (is_null($field_value) && $useDefault) {
        $field_value = $liveuserDB->getOne('
	    SELECT default_value 
	    FROM '.LW_PREFIX.'_prefs_fields 
	    WHERE field_name = ?', 
	    array($field_name));    
    }
    
    return $field_value;
}

/**
 * Sets the specified preference's value for the specified user.
 *
 * @param string username user handle 
 * @param string field_name preference field name
 * @return boolean true if the preference was successfully set, false otherwise
 */
function liveuser_pref_setPref($username, $field_name, $field_value)
{
    global $liveuserDB;
    
    $user_id = liveuser_getPermUserId($username);
    
    /* attempt to fetch existing field_id for the field_name, or create a new field if necessary */
    if (($field_id = liveuser_pref_checkField($field_name)) === false &&
        ($field_id = liveuser_pref_setField($field_name)) === false) {
        return false;
    }
    
    $pref_id = $liveuserDB->getOne('
	SELECT pref_id 
	FROM '.LW_PREFIX.'_prefs_data 
	WHERE user_id = ? AND field_id = ?',
	array((int)$user_id, (int)$field_id));
	    
    if (isset($pref_id) && is_numeric($pref_id)) {
	return ($liveuserDB->query('
	    UPDATE '.LW_PREFIX.'_prefs_data 
	    SET field_value = ? 
	    WHERE pref_id = ?',
	    array($field_value, (int)$pref_id)) == DB_OK);
    } else {
	return ($liveuserDB->query('
	    INSERT INTO '.LW_PREFIX.'_prefs_data (field_value, user_id, field_id) VALUES (?, ?, ?)',
	    array($field_value, (int)$user_id, (int)$field_id)) == DB_OK);
    }       
}

/**
 * Checks if the specified preference field already exists.
 *
 * @param mixed field_name field_name or id of preference field to check for 
 * @param boolean publicOnly if true, only check against public fields
 * @return mixed integer identifier if field exists, false otherwise
 */ 
function liveuser_pref_checkField($field_name, $publicOnly = false)
{
    global $liveuserDB;
    
    if (is_numeric($field_name)) {
	$field_id = $liveuserDB->getOne('
	    SELECT field_id 
	    FROM '.LW_PREFIX.'_prefs_fields 
	    WHERE field_id = ? AND public >= ?',
	    array((int)$field_name, (int)$publicOnly));
    } else {    
	$field_id = $liveuserDB->getOne('
	    SELECT field_id 
	    FROM '.LW_PREFIX.'_prefs_fields 
	    WHERE field_name = ? AND public >= ?',
	    array($field_name, (int)$publicOnly));
    }
    
    return (is_numeric($field_id) ? $field_id : false);
}

/**
 * Removes a field definition and all references in the preferences data table.
 *
 * @param mixed field_name field name or id of preference field 
 * @return boolean true if the field was removed successfully, false otherwise
 */ 
function liveuser_pref_removeField($field_name)
{
    global $liveuserDB;
    
    if (($field_id = liveuser_pref_checkField($field_name)) === false) {
        return false;
    }
    
    if ($liveuserDB->query('DELETE FROM '.LW_PREFIX.'_prefs_data WHERE field_id = ?',
        array((int)field_id)) != DB_OK) {
        return false;
    }
    
    if ($liveuserDB->query('DELETE FROM '.LW_PREFIX.'_prefs_fields WHERE field_id = ?',
        array((int)field_id)) != DB_OK) {
        return false;
    }
}

/**
 * Sets a field (adds or updates) with the specified properties.
 *
 * @param mixed field_name field name or id of preference field 
 * @param boolean public preference field is public if true, private if false
 * @param string default_value default field value if user preference is not set
 * @param string possible_values possible_values field value for admin to set and user to choose
 * @return mixed integer identifier of field if operation was successful, false otherwise
 */ 
function liveuser_pref_setField($field_name, $public = true, $default_value = null, $possible_values = null)
{
    global $liveuserDB;
    
    if (($field_id = liveuser_pref_checkField($field_name)) !== false) {
        return ($liveuserDB->query('
	    UPDATE '.LW_PREFIX.'_prefs_fields 
	    SET public = ?, default_value = ?, possible_values = ?
	    WHERE field_id = ?',
            array((int)$public, $default_value, ($possible_values ? serialize($possible_values) : null), (int)$field_id)) == DB_OK);
    } else {
        if ($liveuserDB->query('
	    INSERT INTO '.LW_PREFIX.'_prefs_fields (field_name, public, default_value, possible_values) 
	    VALUES (?, ?, ?, ?)',
            array($field_name, (int)$public, $default_value, ($possible_values ? serialize($possible_values) : null))) != DB_OK) {
            return false;
        }
        return liveuser_pref_checkField($field_name);
    }
}

?>