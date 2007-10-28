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

// ewiki callback for rights administration page
$ewiki_plugins['page']['AdminRights'] = 'ewiki_page_liveuser_admin_rights';

/**
 * admin gui for modifying LiveWeb rights
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */
function ewiki_page_liveuser_admin_rights($id, $data, $action)
{
    global $liveuserPermAdmin;

    ob_start();
    
    echo ewiki_make_title($id, $id, 2);
           
    // handle posted updates and deletes
    if (isset($_POST['submit_changerights'])) {
        foreach ($_POST as $key => $value) {
            list($prefix, $id) = explode('_',$key,2);
            
            if ($prefix == 'chk' && is_numeric($id) && $value == 'on') {
                if (liveuser_removeEntity('right_id', $id)) {
                    echo '<p>Right '.$id.' was successfully deleted.</p>';	
                } else {
                    echo '<p>Deletion of right '.$id.' failed.</p>';
                }
            }   
        }
    }
    
    // handle posted new rights
    if (isset($_POST['rightname_text']) && isset($_POST['submit_addright'])) {
        $right_id = liveuser_checkEntity('right', $_POST['rightname_text']);
        
        if ($right_id === false) {
            $right_const = 'LU_R_'.strtoupper($_POST['rightname_text']);
	    $right_id = liveuser_addEntity('right', array(LU_AREA_LIVEWEB, $right_const, $_POST['rightname_text']));
            
	    if ($right_id !== false) {
                echo '<p>Right '.$_POST['rightname_text'].' was successfully created.</p>';
            } else {
		echo '<p>Creation of right '.$_POST['rightname_text'].' failed.</p>';
	    }
        } else {
            echo '<p>Right '.$_POST['rightname_text'].' already exists.</p>';
        }
            
        if (isset($_POST['addgroup']) && $right_id !== false) { 
            $group_id = liveuser_checkEntity('group', $_POST['rightname_text']);
            
            if ($group_id === false) {
                $group_const = 'LU_G_'.strtoupper($_POST['rightname_text']);
                $group_id = liveuser_addEntity('group', array($group_const, $_POST['rightname_text'], null, true));
                
                if ($group_id !== false) {
                    echo '<p>Group '.$_POST['rightname_text'].' was successfully created.</p>';
                } else {
                    echo '<p>Creation of group '.$_POST['rightname_text'].' failed.</p>';
                }
            } else {
                echo '<p>Group '.$_POST['rightname_text'].' already exists.</p>';
            }
            
            if ($group_id !== false) {
                // check if group already has the right
                if (liveuser_checkGroupRight($group_id, $right_id)) {
                    echo 'Group '.$_POST['rightname_text'].' already has right '.$_POST['rightname_text'].'.</p>';
                } else {
                    // attempt to assign right to group
                    if ($liveuserPermAdmin->grantGroupRight($group_id, $right_id, 1) === true) {
                        echo '<p>Right '.$_POST['rightname_text'].' has been assigned to group '.$_POST['rightname_text'].'.</p>';
                    } else {
                        echo '<p>Assignment of right '.$_POST['rightname_text'].' to group '.$_POST['rightname_text'].' failed.</p>';
                    }
                }
            }
        }
    }
    
    // Show current table listing of rights
    $rights = $liveuserPermAdmin->getRights();
    
    if (is_array($rights) && !empty($rights)) {
        ?>
            <form method="post" action="">
            <h3>Edit Rights</h3>
            <table border="1">
            <tr><th>Delete</th><th>Right ID</th><th>Right</th></tr>
        <?php
        
        foreach ($rights as $right) {
            ?>
                <tr>
                    <td><input name="chk_<?=$right['right_id']?>" type="checkbox" /></td>
                    <td><?=$right['right_id']?></td>
                    <td><?=$right['name']?></td>
                </tr>
            <?php
        }
        
        ?>
            </table>
            <input type="reset" value="Reset" />
            <input name="submit_changerights" type="submit" value="Submit Changes" />
            </form>
        <?php
    } else {
        ?>
            <h3>Edit Rights</h3>
            <p>No rights were found in the database.</p>
        <?php
    }
    
    // Show Add a new right section
    ?>
        <form method="post" action="">
        <h3>Add a Right</h3>
        <p>When creating a right, you may choose to create a group with the right, which may then be applied to user accounts. If the right already exists, this form will still attempt to link a group to it. If the group already exists and does not have the right, the right will be assigned.</p>
        <label for="rightname_text">Right Name</label>
        <input id="rightname_text" name="rightname_text" type="text" /><br />
        <label for="addgroup">Add/Assign Group</label>
        <input id="addgroup" name="addgroup" type="checkbox" checked="checked" /><br />
        <input name="submit_addright" type="submit" value="Add Right" />
        </form>
    <?php

    $o = ob_get_contents();
    ob_end_clean();
    return $o;
}
?>