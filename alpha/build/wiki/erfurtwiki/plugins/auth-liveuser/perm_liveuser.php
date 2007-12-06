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
 * ewiki: liveuser permissions plugin
 *
 * @author andy fundinger <afundinger@burgiss.com>
 * @author alex wan <alex@burgiss.com>
 * @author jeremy mikola <jmikola@arsjerm.net>
 */

require_once(dirname(__FILE__).'/liveuser_aux.php');
require_once('plugins/lib/protmode.php');

/*
 * auth constants must be defined to override auth_liveuser to never validate,
 * so that any ring test with $ewiki_plugins['auth_perm'] will fail.
 */
define('EWIKI_LOGGEDIN_RING', max($liveuserPermRings) + 1);
define('EWIKI_NOT_LOGGEDIN_RING', max($liveuserPermRings) + 1);
require_once(dirname(__FILE__).'/auth_liveuser.php');

// ewiki callbacks for perm check, and page editing form hooks
$ewiki_plugins['auth_perm'][0]            = 'ewiki_auth_perm_liveuser';
$ewiki_plugins['edit_form_append'][]      = 'ewiki_edit_form_append_liveuser_manage';
$ewiki_plugins['edit_form_append'][]      = 'ewiki_edit_form_append_liveuser_publish';
$ewiki_plugins['edit_save'][]             = 'ewiki_edit_save_liveuser_manage';
$ewiki_plugins['edit_save'][]             = 'ewiki_edit_save_liveuser_publish';
$ewiki_plugins['action_always']['manage'] = 'ewiki_action_manage_liveuser';
$ewiki_plugins['action_binary']['manage'] = 'ewiki_action_manage_liveuser';
$ewiki_plugins['binary_handler'][]        = 'ewiki_binary_handler_liveuser_setdefault_rights';

// html page output response messages
$ewiki_t['en']['MANAGE_TITLE']   = 'Manage This Page';
$ewiki_t['en']['MANAGE_NEWPAGE'] = '<p>The requested page is not available for management.</p>';

/**
 * given a page id and action as input, resolves action to a ring level as
 * defined herein, and then queries a table linking liveuser perms/rights with
 * ewiki page id and ring combinations. the current user is then checked against
 * all possible rights that would satisfy the page id and ring combination.
 * this function alters ewiki_ring to the resolved ring value.
 *
 * @param string page_name page name
 * @param mixed data
 * @param string action page action
 * @param int ewiki_ring ring level corresponding to action (set by reference)
 * @param int request_auth
 * @return boolean true if current user has access, false otherwise  
 */
function ewiki_auth_perm_liveuser($page_name, &$data, $action, $ewiki_ring, $request_auth)
{
    global $liveuser, $liveuserDB, $liveuserPermRings,$ewiki_config;

    // if we are authenticating for the page we are creating
    if ($page_name == $ewiki_config["create"] ) {
        $page_name = '[NewPage]';
    }

    /*
     * checks for an explicit request for admin level rights, requires our top
     * level right on such a request, otherwise we select our own ring based on
     * the internal action table.
     */
    if (($ewiki_ring !== 0) && (array_key_exists($action, $liveuserPermRings))) {
        $ewiki_ring = $liveuserPermRings[$action];
    } else {
        // for unknown actions, require highest perm level (min numerical value)
        $ewiki_ring = min($liveuserPermRings);
    }

    // fetch all perms matching page id and ring level
    $right_ids = $liveuserDB->getCol('SELECT right_id FROM '.LW_PREFIX.'_perms WHERE pagename = ? AND ring <= ?', 0,
        array($page_name, (int)$ewiki_ring));
       
    foreach ($right_ids as $right_id) {

        if ($right_id == LU_R_NOTLOGGEDIN) {
            return true;
	    } else if ($right_id == LU_R_LOGGEDIN && $liveuser->isLoggedIn()) { 
            return true;
	    } else if ($liveuser->checkRight($right_id) && $liveuser->isLoggedIn()) {
            return true;
        }
    }
    
    return false;
}

/**
 * the set of all rights in the system must be filtered by the list of all
 * publically viewable rights, and the resulting list returned as a series of
 * form field selection options. The fourth parameter is used internally to
 * hide the permissions for page editing and defaults to true (edit box is shown).
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @param boolean showEdit shows editing permissions if true, hides if false
 * @return string html output for perm selection fields
 */
function ewiki_edit_form_append_liveuser_manage($id, $data, $action, $showEdit = true)
{
    global $liveuserDB, $liveuserBaseRings, $liveuserDefaultPermsView, $liveuserDefaultPermsEdit;

    $o = '';
    $ewiki_ring = false;
    
    if (!ewiki_auth_perm_liveuser($id, $data, 'manage', $ewiki_ring, 0)) {    
        return '';
    }
    
    $liveuserCurrentPermsView = null;
    $liveuserCurrentPermsEdit = null;
    $selected = false;
    
    // fetch config default rights for new pages, existing set rights otherwise
    if (page_exists($id, $data)) {
        $liveuserCurrentPermsView = $liveuserDB->getCol('SELECT right_id FROM '.LW_PREFIX.'_perms WHERE pagename = ? AND ring <= ?', 0,
            array($id, $liveuserBaseRings['view']));
        $liveuserCurrentPermsEdit = $liveuserDB->getCol('SELECT right_id FROM '.LW_PREFIX.'_perms WHERE pagename = ? AND ring <= ?', 0,
            array($id, $liveuserBaseRings['edit']));
    }
    
    // fetch the set of public permissions
    $rightOptions = liveuser_perm_getPublicPerms();
    
    $o .= '<br /><label for="liveuserPermsView">View Rights (active rights selected)</label><br />';
    $o .= '<select id="liveuserPermsView" name="liveuserPermsView[]" size="5" multiple="multiple">';
    
    // to pre-select perms, check for occurrence in currentPerms if page exists, or defaultPerms otherwise
    foreach ($rightOptions as $value => $label) {
        $selected = (is_array($liveuserCurrentPermsView) ? in_array($value, $liveuserCurrentPermsView) : in_array($label, $liveuserDefaultPermsView));
        $o .= '<option value="'.htmlentities($value).'"'.($selected ? ' selected="selected"' : '').'>'.htmlentities($label).'</option>';
    }
    
    $o .= '</select><br /><br />';
        
    if ($showEdit) {
        $o .= '<label for="liveuserPermsEdit">Edit Rights</label><br />';
        $o .= '<select id="liveuserPermsEdit" name="liveuserPermsEdit[]" size="5" multiple="multiple">';
    
        foreach ($rightOptions as $value => $label) {
            $selected = (is_array($liveuserCurrentPermsEdit) ? in_array($value, $liveuserCurrentPermsEdit) : in_array($label, $liveuserDefaultPermsEdit));
            $o .= '<option value="'.htmlentities($value).'"'.($selected ? ' selected="selected"' : '').'>'.htmlentities($label).'</option>';
        }
    
        $o .= '</select><br /><br />';
    }
   
    return $o;
}

/**
 * if the current user has permission to publish pages (viewable by those not
 * logged in), append the respective checkbox to the edit form.
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string html output for perm selection fields
 */
function ewiki_edit_form_append_liveuser_publish($id, $data, $action)
{
    global $liveuser;

    $o = '';
    
    if ($liveuser->checkRight(LU_R_LW_PUBLISHER)) {
        // check if permission for anonymous users exists
        $published = (liveuser_checkPerm($id, LU_R_NOTLOGGEDIN) !== false);
        $o = '<br /><label for="liveuserPermsPublish">Publish</label> <input type="checkbox" name="liveuserPermsPublish" id="liveuserPermsPublish" value="checked" '.($published ? 'checked="checked" ' : ' ').'/><br />';
    }
    
    return $o;
}

/**
 * A binary handler to adjust rights to default on upload or cache
 *
 * @param string id
 * @param mixed data
 * @param string action
*/ 
function ewiki_binary_handler_liveuser_setdefault_rights($id, &$data, $action)
{
    if ($action == 'save') {
        ewiki_liveuser_setdefault_rights($data);
    }
}


/**
 * called on pages that need to have their default rights set 
 * applied to binary and non-binary pages
 *
 * @param array save associative array of ewiki page data
 * @param action the action by which this page is being reset to default rights
 */
function ewiki_liveuser_setdefault_rights(&$save)
{
    // remove any existing rights
    $perms = liveuser_getPerms($save['id']);
    foreach ($perms as $perm) {
	liveuser_removePerm($save['id'], $perm['right_id']);
    }
    
    if (isset($save['PageType']) && ($save['PageType'] == 'CachedImage')) {
        // if a page type is set, get rights based on the PageType
        $perms = liveuser_getPerms('['.$save['PageType'].']');
    } else {
        // select rights for [NewPage] 
        $perms = liveuser_getPerms('[NewPage]');
    }        
    
    // duplicate them for the page being created
    foreach ($perms as $perm) {
        liveuser_addPerm($save['id'], $perm['ring'], $perm['right_id']);
    }
}


/**
 * iterate over posted form data and extract selected liveuser rights to associate
 * with view and edit permissions for the current ewiki page id.
 *
 * @param array save associative array of ewiki form data
 */
function ewiki_edit_save_liveuser_manage(&$save)
{   
    global $liveuserBaseRings, $liveuserDefaultPerms;

    // determine what form data is available to be processed
    $handleView = isset($_REQUEST['liveuserPermsView']) && is_array($_REQUEST['liveuserPermsView']);
    $handleEdit = isset($_REQUEST['liveuserPermsEdit']) && is_array($_REQUEST['liveuserPermsEdit']);
    
    $ewiki_ring = false;
    
    // set default rights for new pages
        if ($save['version'] == 1) {
            ewiki_liveuser_setdefault_rights($save);
        }

    // if not authorized to manage this page or form data is unavailable quit out.
    if (!ewiki_auth_perm_liveuser($save['id'], $save, 'manage', $ewiki_ring, 0) || !($handleView || $handleEdit)) {
        return;
    } 
        // fetch the set of public permissions
        $rightOptions = liveuser_perm_getPublicPerms();

        /*
         * clear database of all records for public field options for rings levels
         * below edit or view. the minimum removal level (edit or view) will be determined
         * based on whether form data for edit permissions was submitted, or just
         * form data for view.
         */
        if ($handleView || $handleEdit) {
            foreach ($rightOptions as $right_id => $name) {
                liveuser_removePerm($save['id'], $right_id, ($handleEdit ? $liveuserBaseRings['edit'] : $liveuserBaseRings['view']));
            }
        }

        if ($handleView) {
            foreach($_REQUEST['liveuserPermsView'] as $right_id) {
                if (array_key_exists($right_id, $rightOptions)) {
                    liveuser_addPerm($save['id'], $liveuserBaseRings['view'], $right_id, true);
                }
            }    
        }
        
        if ($handleEdit) {
            foreach($_REQUEST['liveuserPermsEdit'] as $right_id) {
                if (array_key_exists($right_id, $rightOptions)) {
                    liveuser_addPerm($save['id'], $liveuserBaseRings['edit'], $right_id, true);
                }
            }    
        }

}

/**
 * if the current user has permission to publish pages, write the respective
 * form data back to the database, thereby allowing a page to be published (with
 * viewing rights for users not logged in) or not published.
 *
 * @param array save associative array of ewiki form data
 */
function ewiki_edit_save_liveuser_publish($save)
{
    global $liveuser, $liveuserBaseRings;

    // alter only if user has publisher right and form field exists
    if ($liveuser->checkRight(LU_R_LW_PUBLISHER)) {
        if (isset($_REQUEST['liveuserPermsPublish']) && $_REQUEST['liveuserPermsPublish'] == 'checked') {
            liveuser_addPerm($save['id'], $liveuserBaseRings['view'], LU_R_NOTLOGGEDIN);
        } else {
            liveuser_removePerm($save['id'], LU_R_NOTLOGGEDIN);
        }
    }
}

function page_exists($id, &$data){
    return(!(empty($data['content'])&& empty($data['meta']) && !array_key_exists($id, $GLOBALS['ewiki_plugins']['page'])));
}

/**
 * this manage action will display a form to edit viewing rights on an internal
 * or binary page. the ability to publish (make publicly viewable) is also
 * provided. 
 *
 * @param mixed id
 * @param mixed data
 * @param string action
 * @return string html output for perm selection fields
 */
function ewiki_action_manage_liveuser($id, $data, $action)
{
    $o = '';
    
    // ignore new pages
    if (!page_exists($id, $data)) {
        $o .= ewiki_make_title('', ewiki_t('MANAGE_TITLE').' » '.$id.' «');
        $o .= ewiki_t('MANAGE_NEWPAGE');
        return $o;
    }
    
    // handle form submission
    if (isset($_REQUEST['submit_manage'])) {
        // process view permissions form data
        ewiki_edit_save_liveuser_manage($data);
        
        // process published-status form data
        ewiki_edit_save_liveuser_publish($data);
    }
    
    // construct manage form
    $o .= ewiki_make_title('', ewiki_t('MANAGE_TITLE').' » '.$id.' «');
    $o .= '<form action="" method="post">';
    $o .= ewiki_edit_form_append_liveuser_manage($id, $data, $action, false);
    $o .= ewiki_edit_form_append_liveuser_publish($id, $data, $action);
    $o .= '<input type="submit" name="submit_manage" /></form>';
    
    return $o;
}

/**
 * fetches a list of all public permissions used in the ewiki page edit form to
 * assign view and edit permissions.
 *
 * @return array set of publically viewable right_id [key] and name [value] pairs
 */
function liveuser_perm_getPublicPerms()
{
    global $liveuser, $liveuserPublicPerms, $liveuserPermAdmin;
    
    $rights = $liveuserPermAdmin->getRights();
    $publicRights = array();
    foreach ($rights as $right) {
        // add only rights whose name exists in the global liveuserPublicPerms array
        if (in_array($right['name'], $liveuserPublicPerms)) {
            $publicRights[$right['right_id']] = $right['name'];
        }
    }
    
    return $publicRights;
}

?>