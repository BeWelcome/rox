<?php
/**
 * Loading defaults
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: defaults.inc.php 147 2006-07-17 18:31:31Z kang $
 */
/**
 * This function returns the default controller (if no valid request is made)
 * 
 * @param void
 * @return string The class name of the default controller
 */
function PPckup() 
{
   	//header('Location: bw/');
    //PPHP::PExit();	
    return 'RoxController';
}

/**
 * This function returns the real application names for several aliases
 * 
 * @param string $request the requested app
 * @return string the real app name
 */
function translate($request) {
    $o = array(
        'theidea' => 'about',
        'thepeople' => 'about',
        'getactive' => 'about',
        'terms' => 'about',
        'bod' => 'about',
        'help' => 'about',
        'terms' => 'about',
        'impressum' => 'about',
        'affiliations' => 'about',
        'privacy' => 'about',
        'stats' => 'about'
    );
    if (array_key_exists(strtolower($request), $o)) {
        return $o[strtolower($request)];
    }
    return $request;
}

// suspended
$susp = $B->x->query('/basedata/suspended');
if ($susp->length > 0) {
	$env = PVars::getObj('env');
    if ($env->suspend_url) {
    	header('Location: '.$env->suspend_url);
    } else {
    	header('HTTP/1.1 403 Forbidden');
    }
    PPHP::PExit();
}
// debug?
$debug = $B->x->query('/basedata/debug');
if ($debug->length > 0) {
    PVars::register('debug', true);
    $build = str_replace(SCRIPT_BASE, '', BUILD_DIR);
    PVars::register('build', substr($build, 0, strlen($build) - 1));
}
?>