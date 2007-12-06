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
require_once(dirname(__FILE__).'/pref_liveuser.php');

// ewiki callback for perms administration page
$ewiki_plugins['page']['AdminPrefs'] = 'ewiki_page_liveuser_admin_prefs';

/**
 * admin gui for modifying LiveUser preferences
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */
function ewiki_page_liveuser_admin_prefs($id, $data, $action)
{
    global $liveuserDB;
    
    ob_start();
    
    echo ewiki_make_title($id, $id, 2);
    
    // delete or update preference fields
    if (isset($_POST['changeprefs_submit'])) {
        
        // extract input fields
        foreach ($_POST as $key => $value) {
            list($prefix, $id) = explode('_', $key, 2);
            
            // deleted checked preferences
            if ($prefix == 'chk' && is_numeric($id) && $value == 'on') {
                if (liveuser_pref_removeField($id)) {
                    echo '<p>Field '.$id.' was successfully deleted</p>.';
                } else {
                    echo '<p>An error occurred deleting field '.$id.'</p>';
                }
            }
            
            // update preference fields for differing new/old values
            if ($prefix == 'public' && is_numeric($id) && ($_POST['origpublic_'.$id] != $value)) {
                echo 'old value: '.$_POST['origpublic_'.$id].'new value: '.$value.'<br />';
            }
            
            if ($prefix == 'default' && is_numeric($id) && ($_POST['origdefault_'.$id] != $value)) {
                echo 'old value: '.$_POST['origdefault_'.$id].'new value: '.$value.'<br />';
            }
        }
    }
    
    // Handle POSTed new rows
    if (!empty($_POST['prefname_text'])) {    
	$livewebpref = liveuser_pref_checkField($_POST['prefname_text']);
	        
        if ($livewebpref === false) {
	    if (!empty($_POST['default_text'])) {
		$livewebpref = $_POST['public_chk'] ? liveuser_pref_setField($_POST['prefname_text'], true, $_POST['default_text']) :
		    liveuser_pref_setField($_POST['prefname_text'], false, $_POST['default_text']);		    
	    } else {
		$livewebpref = $_POST['public_chk'] ? liveuser_pref_setField($_POST['prefname_text'], true) :
		    liveuser_pref_setField($_POST['prefname_text'], false);		    
	    }
	    
            if ($livewebpref !== false ) {
                echo $_POST['prefname_text'].' was inserted into liveweb_prefs_fields...';
            }
        } else {
            echo $_POST['prefname_text'].' could NOT be inserted into liveweb_prefs_fields...';
        }   
    }
   
    echo '<br /><br />';
        
    // Get list of prefs fields in liveuser system
    $results = $liveuserDB->getAll('SELECT * FROM liveweb_prefs_fields');
    
    // show form for modifying preferences
    if (is_array($results) && !empty($results)) {	
	?>
	    <form method="post" action="">
            <h3>Modify Preference Fields</h3>
	    <table border="1">
	    <tr><th>Delete</th><th>ID</th><th>Preference</th><th>Public</th><th>Default Value</th></tr>
	<?php
        
	foreach ($results as $result) {
	    ?>
                <tr>
                    <td><input name="chk_<?=$result['field_id']?>" type="checkbox"></td><td><?=$result['field_id']?></td>
                    <td><?=$result['field_name']?></td>
                    <td>
                        <input name="origpublic_<?=$result['field_id']?>" value="<?=$result['public']?>" type="hidden">
                        <select id="public_<?=$result['field_id']?>" name="public_<?=$result['field_id']?>">
                        <option value="1" <?=(($result['public'] == 1) ? 'selected="selected"' : '')?>>Y</option>
                        <option value="0" <?=(($result['public'] == 0) ? 'selected="selected"' : '')?>>N</option>	
                        </select>
                    </td>
                    <td>
                        <input name="origdefault_<?=$result['field_id']?>" value="<?=$result['default_value']?>" type="hidden">
                        <input name="default_<?=$result['field_id']?>" value="<?=$result['default_value']?>">
                    </td>
                </tr>
            <?php
	}
	
	?>
            </table>
            <input type="reset" text="Reset">
            <input type="submit" name="changeprefs_submit">
            </form>
        <?php
    } else {
        echo '<p>No preference fields were found.</p>';
    }

    // show form for adding preferences
    ?>
        <form method="post" action="">
        <h3>Add a Preference Field</h3>
        <label for="prefname_text">Preference Name</label>
        <input id="prefname_text" name="prefname_text" type="text"><br />
        <label for="public_chk">Public</label>
        <input id="public_chk" name="public_chk" type="checkbox"><br />    
        <label for="default_text">Default Value</label>
        <input id="default_text" name="default_text" type="text"><br />
        <input type="submit" name="addprefs_submit">
        </form>
    <?php
    
    $o = ob_get_contents();
    ob_end_clean();
    return $o;
}

?>