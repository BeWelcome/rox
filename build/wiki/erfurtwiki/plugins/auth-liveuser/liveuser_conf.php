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

require_once('DB.php');

/* constant definitions of liveuser entities should be inserted here. primarily,
 * only area and right constants are necessary. constants for entities made
 * after the setup script (e.g. during plugin usage), may also be added here
 * as the need arises.
 */
/*
 * prefix for internal database table names
 */
define('LW_PREFIX', 'liveuser_plugin');

/*
 * min/max length of randomly generated passwords.
 */ 
define('LW_PASSWORD_LEN_MIN', 7);
define('LW_PASSWORD_LEN_MAX', 10);

/*
 * the liveuserBaseRings array is used internally to denote base action levels,
 * which will be used in assigning permissions. the liveuserPermRings array links
 * ewiki actions to a qualified base ring. the lowest ring level (admin) is 0,
 * and all rings should be at least 0.
 */
$liveuserBaseRings = array('view' => 4, 'forum' => 3, 'edit' => 2, 'manage' => 1, 'admin' => 0);
$liveuserPermRings = array('view'        => $liveuserBaseRings['view'],
                           'info'        => $liveuserBaseRings['view'],
                           'links'       => $liveuserBaseRings['view'],
                           'extodo'      => $liveuserBaseRings['view'],
                           'expolicy'    => $liveuserBaseRings['view'],
                           'search'        => $liveuserBaseRings['view'],
                           'diff'        => $liveuserBaseRings['view'],
                           'calendar'    => $liveuserBaseRings['view'],
                           'binary-cache'  => $liveuserBaseRings['view'],
                           'binary-get'    => $liveuserBaseRings['view'],
                           'sitemap'        => $liveuserBaseRings['view'],
                           'like'           => $liveuserBaseRings['view'],
                           'addpost'     => $liveuserBaseRings['forum'],
                           'addthread'   => $liveuserBaseRings['forum'],
                           'edit'        => $liveuserBaseRings['edit'],
                           'fetchback'   => $liveuserBaseRings['edit'],  
                           'attachments' => $liveuserBaseRings['edit'],
                           'wikidump'       => $liveuserBaseRings['view'],
                           'updformatheader'=> $liveuserBaseRings['manage'],
                           'manage'      => $liveuserBaseRings['manage'],
                           'binary-upload' => $liveuserBaseRings['manage'],
                           'admin'       => $liveuserBaseRings['admin']);

/*
 * the following arrays will be used to filter displayed permission names when
 * editing a page. any permission names not cross-listed in liveuserPublicPerms
 * will be kept hidden (and thus cannot be assigned). any permission names
 * additionally listed in liveuserDefaultPerms will be selected in the form
 * by default.
 */
$liveuserPublicPerms = array('Staff');
$liveuserDefaultPermsView = array('Staff');
$liveuserDefaultPermsEdit = array('Staff');

/*
 * database configuration. the liveuserDSN variable should be set to a valid DSN
 * string according to PEAR::DB specifications.
 * <http://pear.php.net/manual/en/package.database.db.intro-dsn.php>
 *
 * (mysql is the type for mysql databases)
 */
$liveuserDSN = 'type://username:password@server/database';

/*
 * the following array contains liveuser configuration parameters.
 */
$liveuserConfig = array('session'        => array('name' => 'PHPSESSID', 'varname' => 'loginInfo'),
                        'login'          => array('method' => 'post', 'username' => 'username', 'password' => 'password', 'force' => false),
                        'logout'         => array('destroy' => true),
                        'autoInit'       => true,
                        'authContainers' => array(0 => array('name' => 'eWiki',
                                                             'type' => 'DB',
                                                             'dsn' => $liveuserDSN,
                                                             'loginTimeout' => 0,
                                                             'expireTime' => 43200,
                                                             'idleTime' => 7200,
                                                             'allowDuplicateHandles' => 0,
                                                             'authTable' => 'liveuser_users',
                                                             'passwordEncryptionMode' => 'MD5'
                                                             )
                                                  ),
                        'permContainer'  => array('type' => 'DB_Complex',
                                                  'dsn' => $liveuserDSN,                                                                
                                                  'prefix' => 'liveuser_'
                                                  )
                       );

/*
 * The following PEAR error handler may be set to globally handle errors generated
 * by PEAR classes. It provides debug/backtrace output for user-level PHP errors.
 * If used, PHP should be set not to display internal error messages to clients.
 *
 * @param object err PEAR error object 
 */
function _liveuser_error_handler($err) {        
  $log = $err->toString();
  if(function_exists('debug_backtrace')){
        // remove first step from backtrace (this function call), and parse remaining stack trace
        $trace = debug_backtrace();
        array_shift($trace);
        foreach ($trace as $step) {
                $log .= "\n";
                if (isset($step['file'])) $log .= $step['file'].':';
                if (isset($step['line'])) $log .= $step['line'].' ';
                if (isset($step['function'])) {
                        $log .= $step['function'].'(';
                        if (isset($step['args'])) {
                                $args = '';
                                foreach ($step['args'] as $arg) { $args .= '\''.$arg.'\','; }
                                $log .= rtrim($args,',');
                        }
                        $log .= ') ';
                }
        }
  }
        error_log($log."\n", 0);
        exit('<p>An internal error has occurred. Please consult the error logs for additional information</p>');
}

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, '_liveuser_error_handler');
$liveuserDB  =& DB::connect($liveuserDSN);
$liveuserDB->setFetchMode(DB_FETCHMODE_ASSOC);

?>
