<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
if (!version_compare(phpversion(), '5.1.0', '>='))
    die('Only for PHP version 5.1.0 or greater!');
// find out whether the scripts reside in a subdir or not
$script_base = "../../";
if (file_exists($script_base.'/base.xml')) {
    $script_base = str_replace('\\', '/', $script_base).'/';
} elseif (file_exists($script_base.'/../base.xml')) {
    $script_base = str_replace('\\', '/', realpath($script_base.'/..')).'/';
} else {
    die('File "base.xml" not found!');
}
ini_set('display_errors', 1);
ini_set('allow_url_fopen', 1);
/**
 * The directory where the script resides
 */
define('SCRIPT_BASE', $script_base);
/**
 * The directory where the index.php resides
 */
define('HTDOCS_BASE', dirname(__FILE__).'/');
ini_set('error_log', SCRIPT_BASE.'errors.log');
error_reporting(E_ALL);
try {
    /**
     * load base.xml data
     */
    require_once SCRIPT_BASE.'inc/base.inc.php';
    /**
     * load libraries
     */
    require_once SCRIPT_BASE.'lib/libs.php';
    $S = PSurveillance::get();
    /**
     * environment check
     */
    require_once SCRIPT_BASE.'inc/env_check.inc.php';
    /**
     * configuration
     */
    require_once SCRIPT_BASE.'inc/config.inc.php';
    /**
     * defaults
     */
    require_once SCRIPT_BASE.'inc/defaults.inc.php';
    PSurveillance::setPoint('base_loaded');
    
    if (defined ('SESSION_NAME'))
        ini_set ('session.name', SESSION_NAME);
    ini_set ('session.use_trans_sid', 0);
    ini_set ('url_rewrite.tags', '');
    ini_set ('session.hash_bits_per_character', 6);
    ini_set ('session.hash_function', 1);
    session_start();
    if (empty ($_COOKIE[session_name ()]) ) {
        PVars::register('cookiesAccepted', false);
    } else {
        PVars::register('cookiesAccepted', true);
    }
    PVars::register('queries', 0);
    
    PSurveillance::setPoint('loading_modules');
    // load modules
    $Mod = PModules::get();
    $Mod->setModuleDir(SCRIPT_BASE.'modules');
    $Mod->loadModules();
    PSurveillance::setPoint('modules_loaded');
            
    $Apps = PApps::get();
    $Apps->build();
    // process includes
    $includes = $Apps->getIncludes();
    if ($includes) {
        foreach ($includes as $inc) {
            require_once $inc;
        }
    }
} catch (PException $e) {
    header('Content-type: application/xml; charset=utf-8');
    echo $e;
    exit();
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage();
    echo "\n{$e->getFile()} ({$e->getLine()})";
    exit();
}    
?>