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
 * note: this setup file will build database tables used by this plugin (LiveUser
 * tables must be generated separately), and populate the tables with the configuration
 * specified herein. This script is intended to be run from the command line, and will
 * generate PHP code which should be inserted into liveuser_conf.php, as it will contain
 * several defined constants. This file should be edited to customize rights, groups, and
 * users. Further configuration may be done after the installation process via the
 * admin gui plugin pages.
 *
 * the defined constants must be kept in sync with the database to ensure proper
 * operation of this plugin. for this reason, it is possible to run this script
 * over again to regenerate the definition code based on the current database
 * state; the current id's for each entity (based on a name match) would be retrieved,
 * and any entities that did not exist would be created again.
 *
 * specifically, the only necessary defined constants include the 'AREA' entity, and
 * the LoggedIn, NotLoggedIn, and 'LW_'-prefixed rights. While other constants
 * specified in this script are outputted as a convenience, they are not required
 * to be inserted into liveuser_conf.php.
 */

require_once('LiveUser/LiveUser.php');
require_once('LiveUser/Admin/Auth/Container/DB.php');
require_once('LiveUser/Admin/Perm/Container/DB_Complex.php');
require_once(dirname(__FILE__).'/liveuser_aux.php');

// fetch a list of all existing wiki pagenames, which will be given default permissions
$liveuserWikiDB = DB::connect('type://username:password@server/database');
$liveuserWikiDB->setFetchMode(DB_FETCHMODE_ASSOC);
$pagenames = $liveuserWikiDB->getAll('SELECT DISTINCT pagename FROM ewiki');
$liveuserWikiDB->disconnect();

/*
 * if necessary, override the database connection in liveuser_conf.php with one
 * that has CREATE/DROP rights. comment these lines otherwise.
 */
// $liveuserDB->disconnect();
// $liveuserDB =& DB::connect('type://username:password@server/database');

// create plugin tables
$liveuserDB->query('
   CREATE TABLE IF NOT EXISTS '.LW_PREFIX.'_perms (
   id int(10) unsigned not null auto_increment,
   pagename varchar(160) binary not null,
   ring tinyint(3) unsigned not null,
   right_id int(10) not null,
   primary key (id),
   unique pagerule (pagename, right_id))
   type=MyISAM;');

// liveuser prefs plugin tables
$liveuserDB->query('
   CREATE TABLE IF NOT EXISTS '.LW_PREFIX.'_prefs_data (
   pref_id int(10) unsigned not null auto_increment,
   user_id int(10) not null,
   field_id int(10) not null,
   field_value mediumblob,
   primary key (pref_id),
   unique prefrule (user_id, field_id))
   type=MyISAM;');
   
$liveuserDB->query('
   CREATE TABLE IF NOT EXISTS '.LW_PREFIX.'_prefs_fields (
   field_id int(10) unsigned not null auto_increment,
   field_name varchar(255),
   public tinyint(1),
   default_value mediumblob,
   primary key (field_id),
   unique fieldrule (field_name))
   type=MyISAM;');  

$liveuserDB->query("
   CREATE TABLE IF NOT EXISTS ".LW_PREFIX."_login_log (
  auth_user_handle varchar(32) NOT NULL default '',
  php_session_id varchar(40) NOT NULL default '',
  ssl_session_id varchar(16) default '',
  ip_address varchar(16) NOT NULL default '',
  time timestamp(14) NOT NULL,
  delay tinyint(1) NOT NULL default '0',
  success tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (time,auth_user_handle)
  ) TYPE=MyISAM; ");


/*
 * the defs array will accumulate all created entities, that should become defined
 * constants to be inserted into the main config file. created entities will include
 * the language (default English, also set in liveuser_aux.php), application
 * name and area (arbitrary values), right and group names, and users. All groups
 * should correspond to a particular right of the same name.
 */
$defs = array();

$defs['LU_LANG'] = liveuser_addEntity('language', array('EN', 'English', 'English'));
$defs['LU_APP']  = liveuser_addEntity('application', array('LU_APP', 'AppName'));
$defs['LU_AREA'] = liveuser_addEntity('area', array($defs['LU_APP'], 'LU_AREA', 'AppArea'));

$rights = array(
    array($defs['LU_AREA'], 'LU_R_NOTLOGGEDIN', 'NotLoggedIn'),
    array($defs['LU_AREA'], 'LU_R_LOGGEDIN', 'LoggedIn'),
    array($defs['LU_AREA'], 'LU_R_LW_PUBLISHER', 'LW_Publisher'),
    array($defs['LU_AREA'], 'LU_R_LW_ADMIN', 'LW_Admin'),
    array($defs['LU_AREA'], 'LU_R_STAFF', 'Staff'));
    
foreach ($rights as $right) {
    $defs[$right[1]] = liveuser_addEntity('right', $right);
}

$groups = array(
    array('LU_G_LW_PUBLISHER', 'LW_Publisher', null, true),    
    array('LU_G_LW_ADMIN', 'LW_Admin', null, true),
    array('LU_G_STAFF', 'Staff', null, true));
    
foreach ($groups as $group) {
    $setup_defs[$group[0]] = liveuser_addEntity('group', $group);
    
    if (liveuser_checkGroupRight($defs[$group[0]], $defs['LU_R_'.strtoupper($group[1])]) === false) {
	$liveuserPermAdmin->grantGroupRight($defs[$group[0]], $defs['LU_R_'.strtoupper($group[1])], 1);
    }    
} 

// LiveUser plugin pages
liveuser_addPerm('AdminPerms', $liveuserBaseRings['view'], $defs['LU_R_LW_ADMIN']);
liveuser_addPerm('AdminRights', $liveuserBaseRings['view'], $defs['LU_R_LW_ADMIN']);
liveuser_addPerm('AdminAddUsers', $liveuserBaseRings['view'], $defs['LU_R_LW_ADMIN']);
liveuser_addPerm('AdminUsers', $liveuserBaseRings['view'], $defs['LU_R_LW_ADMIN']);
// default list of eWiki plugins
liveuser_addPerm('LogIn', $liveuserBaseRings['view'], $defs['LU_R_NOTLOGGEDIN']);
liveuser_addPerm('Login', $liveuserBaseRings['view'], $defs['LU_R_NOTLOGGEDIN']);
liveuser_addPerm('LogOut', $liveuserBaseRings['view'], $defs['LU_R_LOGGEDIN']);
liveuser_addPerm('Logout', $liveuserBaseRings['view'], $defs['LU_R_LOGGEDIN']);
liveuser_addPerm('ChangePassword', $liveuserBaseRings['view'], $defs['LU_R_LOGGEDIN']);
liveuser_addPerm('ProtectedEmail', $liveuserBaseRings['view'], $defs['LU_R_NOTLOGGEDIN']);
liveuser_addPerm('PowerSearch', $liveuserBaseRings['view'], $defs['LU_R_LOGGEDIN']);
liveuser_addPerm('SearchPages', $liveuserBaseRings['view'], $defs['LU_R_LOGGEDIN']);
liveuser_addPerm('Search', $liveuserBaseRings['view'], $defs['LU_R_LOGGEDIN']);
liveuser_addPerm('PageIndex', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('WordIndex', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('PageCalendar', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('PageYearCalendar', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('OrphanedPages', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('NewestPages', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('MostVisitedPages', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('MostOftenChangedPages', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('UpdatedPages', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('FileUpload', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('FileDownload', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
liveuser_addPerm('AboutPlugins', $liveuserBaseRings['view'], $defs['LU_R_STAFF']);
// ability to create new pages
liveuser_addPerm('[NewPage]', $liveuserBaseRings['manage'], $defs['LU_R_STAFF']);

// set rights on all existing pagename's 
foreach ($pagenames as $pagename) {    
    liveuser_addPerm($pagename['pagename'], $liveuserBaseRings['manage'], $defs['LU_R_STAFF']);
}

echo "// add the following lines to liveuser_conf.php\n";
foreach ($defs as $key => $value) {
    echo 'define(\''.$key.'\', '.(is_numeric($value) ? $value : '\''.$value.'\'').");\n";
    define($key,$value);
}

// default user list
$users = array(
    'user1',
    'user2',
    'user3');

// add users (password = username) and set group memberships for default users
foreach ($users as $user) {
    if (liveuser_checkEntity('user', $user) === false) {
	if (liveuser_addEntity('user', array($user, $user)) !== false) {
	    echo 'added user '.$user."\n";
	}
        
	$id = liveuser_checkEntity('user', $user);
	if ($id !== false && $liveuserPermAdmin->addUserToGroup($id, $setup_defs['LU_G_STAFF'])) {
	    echo 'added user '.$user.' to group Staff'."\n";	
	}	
    }
}

// set special group membership for a user
$user1 = liveuser_checkEntity('user', 'user1');
if (liveuser_checkGroupUser($setup_defs['LU_G_LW_PUBLISHER'], $user1) === false) {
    if ($liveuserPermAdmin->addUserToGroup($user1, $setup_defs['LU_G_LW_PUBLISHER'])) {
	echo 'added user user1 to group Publisher'."\n";
    }   
}
if (liveuser_checkGroupUser($setup_defs['LU_G_LW_ADMIN'], $user1) === false) {
    if ($liveuserPermAdmin->addUserToGroup($user1, $setup_defs['LU_G_LW_ADMIN'])) {
	echo 'added user user1 to group Admin'."\n";
    }   
}

?>