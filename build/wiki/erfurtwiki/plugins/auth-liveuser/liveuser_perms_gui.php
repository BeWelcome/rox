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

// ewiki callback for perms administration page
$ewiki_plugins['page']['AdminPerms'] = 'ewiki_page_liveuser_admin_perms';
$ewiki_plugins['page']['AdminPermsReport'] = 'ewiki_page_liveuser_admin_perms';

/**
 * admin gui for modifying LiveUser perms
 *
 * @param string id
 * @param mixed data
 * @param string action
 * @return string page output response
 */
function ewiki_page_liveuser_admin_perms($id, $data, $action)
{
    global $liveuserDB, $liveuserPermAdmin, $liveuserBaseRings;    
    
    ob_start();    
    
    // flip livewebRings keys to values, vice versa
    $ringdefs = array_flip($liveuserBaseRings);
    
    // check if viewing read only
    $readonly = ($id == 'AdminPermsReport');
    
    // preserve filters across forms
    $preservePageFilter = (isset($_REQUEST['pagefilter']) ? $_REQUEST['pagefilter'] : '');
    $preserveClassFilter = (isset($_POST['classfilter']) ? $_POST['classfilter'] : '');
    $preserveLetterFilter = (isset($_REQUEST['letterfilter']) ? $_REQUEST['letterfilter'] : '');
    
    echo ewiki_make_title($id, $id, 2);

    $rights = $liveuserPermAdmin->getRights();
    
    if (!$readonly) {
	// Handle POSTed deletes or updates
	foreach ($_POST as $key => $value) {
	    list($prefix, $id) = explode('_',$key,2);
	    
	    if ($prefix == 'chk' && is_numeric($id) && $value == 'on' && isset($_POST['submit_changeperm'])) {
		if (liveuser_removePerm($id)) {
		    echo '<p>Permission '.$id.' was successfully deleted.</p>';
		} else {
		    echo '<p>Deletion of permission '.$id.' failed.</p>';
		}
	    }
	    
	    if ($prefix == 'ring' && is_numeric($id) && $value != '-1' && isset($_POST['submit_changeperm'])) {
		if (liveuser_addPerm($id, $value)) {
		    echo '<p>Permission '.$id.' was successfully updated.</p>';	
		} else {
		    echo '<p>Update of permission '.$id.' failed.</p>';
		}
	    }
	}
	
	// Handle POSTed new rows
	if (!empty($_POST['pagename_text']) && !empty($_POST['right_list']) && isset($_POST['submit_addperm'])) {    
	    $livewebperm = liveuser_checkPerm($_POST['pagename_text'], $_POST['right_list']);
	    
	    if ($livewebperm === false) {
		$livewebperm = liveuser_addPerm($_POST['pagename_text'], $_POST['ring_list'], $_POST['right_list']);	
			
		if ($livewebperm !== false ) {
		    echo '<p>Permission for '.$_POST['pagename_text'].' was successfully created.</p>';
		} else {
		    echo '<p>Creation of permission for '.$_POST['pagename_text'].' failed.</p>';
		}
	    } else {
		echo '<p>Permission for '.$_POST['pagename_text'].' with class '.$_POST['right_list'].' already exists.</p>';
	    }
	}
   
	// Show Add a new row section
	?>
	    <form method="post" action="">
	    <h3>Add a Page Permission</h3>
            <?=(empty($preservePageFilter) ? '' : '<input type="hidden" name="pagefilter" value="'.$preservePageFilter.'" />')?>
            <?=(empty($preserveClassFilter) ? '' : '<input type="hidden" name="classfilter" value="'.$preserveClassFilter.'" />')?>
            <?=(empty($preserveLetterFilter) ? '' : '<input type="hidden" name="letterfilter" value="'.$preserveLetterFilter.'" />')?>
	    <label for="pagename_text">Page Name</label>
	    <input id="pagename_text" name="pagename_text" type="text" /><br />
	    <label for="ring_list">Permission Level</label>
	    <select id="ring_list" name="ring_list">
        <?php
	    
	foreach ($ringdefs as $key => $value) {
	    echo '<option value="'.$key.'">'.$value.'</option>';
	}
	
	?>
	    </select><br />
	    <label for="right_list">Classes</label>
	    <select id="right_list" name="right_list">
        <?php
	    
	foreach ($rights as $right) {
	    echo '<option value="'.$right['right_id'].'">'.$right['define_name'].'</option>';
	}
	
	?>
	    </select><br />
	    <input type="submit" name="submit_addperm" value="Create Permission" />
	    </form>
        <?php
    }
    
    // Show filtering form
    ?>
        <form method="post" action="<?=ewiki_script('', $data['id'])?>">
        <h3>Filter Permissions</h3>
        <table>
        <tr>
            <td>
                <label for="pagefilter">Page Name</label>
                <input id="pagefilter" name="pagefilter" type="text" /><br />
            </td>
            <td>
                <label for="classfilter">Class</label>
                <select id="classfilter" name="classfilter">
                <option value=""></option>
    <?php
        
    foreach ($rights as $right) {
        echo '<option value="'.$right['right_id'].'">'.$right['define_name'].'</option>';
    }
    
    ?>
                </select>
            </td>
            <td><input type="submit" name="submit_filterperm" value="Filter" /></td>
        </tr><tr><td colspan="3"><label>First Letter</label>&nbsp;&nbsp;
    <?php
    
    foreach(range('A', 'Z') as $letter) {
	echo '<a href="'.ewiki_script('', $data['id'], array('letterfilter' => $letter)).'">'.$letter.'</a>&nbsp;';	
    }
    
    ?>
	<a href="<?=ewiki_script('', $data['id'], array('letterfilter' => '0-9'))?>">0-9</a>
	<a href="<?=ewiki_script('', $data['id'], array('letterfilter' => 'other'))?>">Other</a>
	<a href="<?=ewiki_script('', $data['id'], array('letterfilter' => 'all'))?>">All</a>
	</td></tr></table>
        </form>
    <?php
    
    // Show current table listing of pages and permissions
    $query = '
        SELECT '.LW_PREFIX.'_perms.id, '.LW_PREFIX.'_perms.pagename, '.LW_PREFIX.'_perms.ring, liveuser_rights.right_define_name 
        FROM '.LW_PREFIX.'_perms, liveuser_rights
        WHERE '.LW_PREFIX.'_perms.right_id = liveuser_rights.right_id';
    
    $filter = '';
    
    if (!empty($_REQUEST['pagefilter'])) {
        $filter .= ' AND UPPER('.LW_PREFIX.'_perms.pagename) LIKE "%'.strtoupper($_REQUEST['pagefilter']).'%"';    
    }
    
    if (!empty($_POST['classfilter'])) {    
        $filter .= ' AND '.LW_PREFIX.'_perms.right_id = '.$_POST['classfilter'];
    }
    
    if (!empty($_REQUEST['letterfilter'])) {	
	if (strlen($_REQUEST['letterfilter']) == 1 && $_REQUEST['letterfilter'] >= 'A' && $_REQUEST['letterfilter'] <= 'Z') {
	    $filter = ' AND UPPER('.LW_PREFIX.'_perms.pagename) LIKE "'.$_REQUEST['letterfilter'].'%"';
	}
	
	if ($_REQUEST['letterfilter'] == '0-9') {
	    $filter = ' AND '.LW_PREFIX.'_perms.pagename REGEXP "^[0-9]"';
	}
    
	if ($_REQUEST['letterfilter'] == 'other') {
	    $filter = ' AND '.LW_PREFIX.'_perms.pagename REGEXP "^[^0-9A-Za-z]"';
	}
    }
    
    $query .= $filter;
    $query .= ' ORDER BY '.LW_PREFIX.'_perms.pagename ASC';
   
    if (isset($_REQUEST['pagefilter']) || isset($_POST['classfilter']) || isset($_REQUEST['letterfilter'])) {	
	$perms = $liveuserDB->getAll($query);
	
	if (is_array($perms) && !empty($perms)) {	    
	    if (!$readonly) {
		// Display regular AdminPerms page		
		?>
		    <form method="post" action="">
		    <?=(empty($preservePageFilter) ? '' : '<input type="hidden" name="pagefilter" value="'.$preservePageFilter.'" />')?>
		    <?=(empty($preserveClassFilter) ? '' : '<input type="hidden" name="classfilter" value="'.$preserveClassFilter.'" />')?>
		    <?=(empty($preserveLetterFilter) ? '' : '<input type="hidden" name="letterfilter" value="'.$preserveLetterFilter.'" />')?>
                    <h3>Edit Permissions</h3>
		    <table border="1">
		    <tr><th>Delete</th><th>Page Name</th><th>Permission Level</th><th>Class</th></tr>
                <?php
		
		foreach ($perms as $perm) {		
		    ?>
			<tr>
                            <td><input name="chk_<?=$perm['id']?>" type="checkbox" /></td>
                            <td><a href="<?=ewiki_script($perm['pagename'])?>"><?=$perm['pagename']?></a></td>
                            <td><select name="ring_<?=$perm['id']?>">
                    <?php
		    
		    foreach ($ringdefs as $key=>$value) {
			if ($key == $perm['ring']) {
			    echo '<option value="-1" selected>'.$value.'</option>';
			} else {
			    echo '<option value="'.$key.'">'.$value.'</option>';
			}
		    }
		
		    ?>
                            </select></td>
                            <td><?=$perm['right_define_name']?></td>
                        </tr>
                    <?php
		}
		
		?>
                    </table>
                    <input type="reset" value="Reset" />
                    <input name="submit_changeperm" type="submit" value="Submit Changes" />
                    </form>
                <?php
	    } else {
		// Display readonly AdminPermsReport page
		?>
                    <h3>View Permissions</h3>
		    <table border="1">
		    <tr><th>Pagename</th><th>Perm Level</th><th>Class</th></tr>
                <?php
		
		foreach ($perms as $perm) {		
		    ?>
			<tr>
                            <td><a href ="<?=ewiki_script($perm['pagename'])?>"><?=$perm['pagename']?></a></td>
                            <td><?=$ringdefs[$perm['ring']]?></td>
                            <td><?=$perm['right_define_name']?></td>
                        </tr>
                    <?php			
		}
		
		echo '</table>';
	    }
	} else {
            ?>
                <h3><?=($readonly ? 'View' : 'Edit')?> Permissions</h3>
                <p>No permissions were found in the database.</p>
            <?php
        }
    }
    
    $o = ob_get_contents();
    ob_end_clean();
    return $o;
}
?>