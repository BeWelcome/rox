<?php
/**
 * HC external auth request
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
if (!version_compare(phpversion(), '5.1.0', '>='))
    die('Only for PHP version 5.1.0 or greater!');
// find out whether the scripts reside in a subdir or not
$script_base = dirname($_SERVER['SCRIPT_FILENAME']);
if (file_exists($script_base.'/base.xml')) {
    $script_base = str_replace('\\', '/', $script_base).'/';
} elseif (file_exists($script_base.'/../base.xml')) {
    $script_base = str_replace('\\', '/', realpath($script_base.'/..')).'/';
} else {
    die('File "base.xml" not found!');
}
/**
 * The directory where the script resides
 */
ini_set('allow_url_fopen', 1);
define('SCRIPT_BASE', $script_base);
define('HTDOCS_BASE', dirname(__FILE__).'/');
error_reporting(E_ALL);
try {
    require_once SCRIPT_BASE.'lib/libs.php';
    $S = PSurveillance::get();
    require_once SCRIPT_BASE.'inc/env_check.inc.php';
    require_once SCRIPT_BASE.'inc/config.inc.php';
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
    
    if (!function_exists('PPckup')) {
        throw new PException('No default pickup!');
    } 
    
    PSurveillance::setPoint('loading_apps');

    $HC = new HcifController;
    $HC->exAuth();

    PSurveillance::setPoint('apps_loaded');
} catch (PException $e) {
    header('Content-type: application/xml; charset=utf-8');
    echo $e;
    exit();
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage();
    echo "\n{$e->getFile()} ({$e->getLine()})";
    exit();
}
session_write_close();
?>