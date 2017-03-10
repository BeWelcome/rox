<?php
/**
 * Initialize some defaults needed for DokuWiki
 */

/**
 * timing Dokuwiki execution
 */
function delta_time($start=0) {
    return microtime(true)-((float)$start);
}
define('DOKU_START_TIME', delta_time());

global $config_cascade;
$config_cascade = array();

// if available load a preload config file
$preload = fullpath(dirname(__FILE__)).'/preload.php';
if (file_exists($preload)) include($preload);

// define the include path
if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../').'/');

// define Plugin dir
if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

// define config path (packagers may want to change this to /etc/dokuwiki/)
if(!defined('DOKU_CONF')) define('DOKU_CONF',DOKU_INC.'conf/');

// check for error reporting override or set error reporting to sane values
if (!defined('DOKU_E_LEVEL') && file_exists(DOKU_CONF.'report_e_all')) {
    define('DOKU_E_LEVEL', E_ALL);
}
if (!defined('DOKU_E_LEVEL')) {
    if(defined('E_DEPRECATED')){ // since php 5.3, since php 5.4 E_STRICT is part of E_ALL
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    }else{
        error_reporting(E_ALL ^ E_NOTICE);
    }
} else {
    error_reporting(DOKU_E_LEVEL);
}

// init memory caches
global $cache_revinfo;
       $cache_revinfo = array();
global $cache_wikifn;
       $cache_wikifn = array();
global $cache_cleanid;
       $cache_cleanid = array();
global $cache_authname;
       $cache_authname = array();
global $cache_metadata;
       $cache_metadata = array();

// always include 'inc/config_cascade.php'
// previously in preload.php set fields of $config_cascade will be merged with the defaults
include(DOKU_INC.'inc/config_cascade.php');

//prepare config array()
global $conf;
$conf = array();

// load the global config file(s)
foreach (array('default','local','protected') as $config_group) {
    if (empty($config_cascade['main'][$config_group])) continue;
    foreach ($config_cascade['main'][$config_group] as $config_file) {
        if (file_exists($config_file)) {
            include($config_file);
        }
    }
}

//prepare license array()
global $license;
$license = array();

// load the license file(s)
foreach (array('default','local') as $config_group) {
    if (empty($config_cascade['license'][$config_group])) continue;
    foreach ($config_cascade['license'][$config_group] as $config_file) {
        if(file_exists($config_file)){
            include($config_file);
        }
    }
}

// set timezone (as in pre 5.3.0 days)
date_default_timezone_set(@date_default_timezone_get());

// define baseURL
if(!defined('DOKU_REL')) define('DOKU_REL',getBaseURL(false));
if(!defined('DOKU_URL')) define('DOKU_URL',getBaseURL(true));
if(!defined('DOKU_BASE')){
    if($conf['canonical']){
        define('DOKU_BASE',DOKU_URL);
    }else{
        define('DOKU_BASE',DOKU_REL);
    }
}

// define whitespace
if(!defined('DOKU_LF')) define ('DOKU_LF',"\n");
if(!defined('DOKU_TAB')) define ('DOKU_TAB',"\t");

// define cookie and session id, append server port when securecookie is configured FS#1664
if (!defined('DOKU_COOKIE')) define('DOKU_COOKIE', 'DW'.md5(DOKU_REL.(($conf['securecookie'])?$_SERVER['SERVER_PORT']:'')));


// define main script
if(!defined('DOKU_SCRIPT')) define('DOKU_SCRIPT','doku.php');

// DEPRECATED, use tpl_basedir() instead
if(!defined('DOKU_TPL')) define('DOKU_TPL',
        DOKU_BASE.'lib/tpl/'.$conf['template'].'/');

// DEPRECATED, use tpl_incdir() instead
if(!defined('DOKU_TPLINC')) define('DOKU_TPLINC',
        DOKU_INC.'lib/tpl/'.$conf['template'].'/');

// make session rewrites XHTML compliant
@ini_set('arg_separator.output', '&amp;');

// make sure global zlib does not interfere FS#1132
@ini_set('zlib.output_compression', 'off');

// increase PCRE backtrack limit
@ini_set('pcre.backtrack_limit', '20971520');

// enable gzip compression if supported
$conf['gzip_output'] &= (strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false);
global $ACT;
if ($conf['gzip_output'] &&
        !defined('DOKU_DISABLE_GZIP_OUTPUT') &&
        function_exists('ob_gzhandler') &&
        // Disable compression when a (compressed) sitemap might be delivered
        // See https://bugs.dokuwiki.org/index.php?do=details&task_id=2576
        $ACT != 'sitemap') {
    ob_start('ob_gzhandler');
}

// init session
if(!headers_sent() && !defined('NOSESSION')) {
    if(!defined('DOKU_SESSION_NAME'))     define ('DOKU_SESSION_NAME', "DokuWiki");
    if(!defined('DOKU_SESSION_LIFETIME')) define ('DOKU_SESSION_LIFETIME', 0);
    if(!defined('DOKU_SESSION_PATH')) {
        $cookieDir = empty($conf['cookiedir']) ? DOKU_REL : $conf['cookiedir'];
        define ('DOKU_SESSION_PATH', $cookieDir);
    }
    if(!defined('DOKU_SESSION_DOMAIN'))   define ('DOKU_SESSION_DOMAIN', '');

    session_name(DOKU_SESSION_NAME);
    session_set_cookie_params(DOKU_SESSION_LIFETIME, DOKU_SESSION_PATH, DOKU_SESSION_DOMAIN, ($conf['securecookie'] && is_ssl()), true);
    session_start();

    // load left over messages
    if(isset($_SESSION[DOKU_COOKIE]['msg'])) {
        $MSG = $_SESSION[DOKU_COOKIE]['msg'];
        unset($_SESSION[DOKU_COOKIE]['msg']);
    }
}

// kill magic quotes
if (get_magic_quotes_gpc() && !defined('MAGIC_QUOTES_STRIPPED')) {
    if (!empty($_GET))    remove_magic_quotes($_GET);
    if (!empty($_POST))   remove_magic_quotes($_POST);
    if (!empty($_COOKIE)) remove_magic_quotes($_COOKIE);
    if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
    @ini_set('magic_quotes_gpc', 0);
    define('MAGIC_QUOTES_STRIPPED',1);
}
if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);
@ini_set('magic_quotes_sybase',0);

// don't let cookies ever interfere with request vars
$_REQUEST = array_merge($_GET,$_POST);

// we don't want a purge URL to be digged
if(isset($_REQUEST['purge']) && !empty($_SERVER['HTTP_REFERER'])) unset($_REQUEST['purge']);

// precalculate file creation modes
init_creationmodes();

// make real paths and check them
init_paths();
init_files();

// setup plugin controller class (can be overwritten in preload.php)
$plugin_types = array('auth', 'admin','syntax','action','renderer', 'helper','remote');
global $plugin_controller_class, $plugin_controller;
if (empty($plugin_controller_class)) $plugin_controller_class = 'Doku_Plugin_Controller';

// load libraries
require_once(DOKU_INC.'vendor/autoload.php');
require_once(DOKU_INC.'inc/load.php');

// disable gzip if not available
define('DOKU_HAS_BZIP', function_exists('bzopen'));
define('DOKU_HAS_GZIP', function_exists('gzopen'));
if($conf['compression'] == 'bz2' && !DOKU_HAS_BZIP) {
    $conf['compression'] = 'gz';
}
if($conf['compression'] == 'gz' && !DOKU_HAS_GZIP) {
    $conf['compression'] = 0;
}

// input handle class
global $INPUT;
$INPUT = new Input();

// initialize plugin controller
$plugin_controller = new $plugin_controller_class();

// initialize the event handler
global $EVENT_HANDLER;
$EVENT_HANDLER = new Doku_Event_Handler();

$local = $conf['lang'];
trigger_event('INIT_LANG_LOAD', $local, 'init_lang', true);


// setup authentication system
if (!defined('NOSESSION')) {
    auth_setup();
}

// setup mail system
mail_setup();

/**
 * Checks paths from config file
 */
function init_paths(){
    global $conf;

    $paths = array('datadir'   => 'pages',
            'olddir'    => 'attic',
            'mediadir'  => 'media',
            'mediaolddir' => 'media_attic',
            'metadir'   => 'meta',
            'mediametadir' => 'media_meta',
            'cachedir'  => 'cache',
            'indexdir'  => 'index',
            'lockdir'   => 'locks',
            'tmpdir'    => 'tmp');

    foreach($paths as $c => $p) {
        $path = empty($conf[$c]) ? $conf['savedir'].'/'.$p : $conf[$c];
        $conf[$c] = init_path($path);
        if(empty($conf[$c]))
            nice_die("The $c ('$p') at $path is not found, isn't accessible or writable.
                You should check your config and permission settings.
                Or maybe you want to <a href=\"install.php\">run the
                installer</a>?");
    }

    // path to old changelog only needed for upgrading
    $conf['changelog_old'] = init_path((isset($conf['changelog']))?($conf['changelog']):($conf['savedir'].'/changes.log'));
    if ($conf['changelog_old']=='') { unset($conf['changelog_old']); }
    // hardcoded changelog because it is now a cache that lives in meta
    $conf['changelog'] = $conf['metadir'].'/_dokuwiki.changes';
    $conf['media_changelog'] = $conf['metadir'].'/_media.changes';
}

/**
 * Load the language strings
 *
 * @param string $langCode language code, as passed by event handler
 */
function init_lang($langCode) {
    //prepare language array
    global $lang, $config_cascade;
    $lang = array();

    //load the language files
    require(DOKU_INC.'inc/lang/en/lang.php');
    foreach ($config_cascade['lang']['core'] as $config_file) {
        if (file_exists($config_file . 'en/lang.php')) {
            include($config_file . 'en/lang.php');
        }
    }

    if ($langCode && $langCode != 'en') {
        if (file_exists(DOKU_INC."inc/lang/$langCode/lang.php")) {
            require(DOKU_INC."inc/lang/$langCode/lang.php");
        }
        foreach ($config_cascade['lang']['core'] as $config_file) {
            if (file_exists($config_file . "$langCode/lang.php")) {
                include($config_file . "$langCode/lang.php");
            }
        }
    }
}

/**
 * Checks the existence of certain files and creates them if missing.
 */
function init_files(){
    global $conf;

    $files = array($conf['indexdir'].'/page.idx');

    foreach($files as $file){
        if(!file_exists($file)){
            $fh = @fopen($file,'a');
            if($fh){
                fclose($fh);
                if(!empty($conf['fperm'])) chmod($file, $conf['fperm']);
            }else{
                nice_die("$file is not writable. Check your permissions settings!");
            }
        }
    }
}

/**
 * Returns absolute path
 *
 * This tries the given path first, then checks in DOKU_INC.
 * Check for accessibility on directories as well.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function init_path($path){
    // check existence
    $p = fullpath($path);
    if(!file_exists($p)){
        $p = fullpath(DOKU_INC.$path);
        if(!file_exists($p)){
            return '';
        }
    }

    // check writability
    if(!@is_writable($p)){
        return '';
    }

    // check accessability (execute bit) for directories
    if(@is_dir($p) && !file_exists("$p/.")){
        return '';
    }

    return $p;
}

/**
 * Sets the internal config values fperm and dperm which, when set,
 * will be used to change the permission of a newly created dir or
 * file with chmod. Considers the influence of the system's umask
 * setting the values only if needed.
 */
function init_creationmodes(){
    global $conf;

    // Legacy support for old umask/dmask scheme
    unset($conf['dmask']);
    unset($conf['fmask']);
    unset($conf['umask']);
    unset($conf['fperm']);
    unset($conf['dperm']);

    // get system umask, fallback to 0 if none available
    $umask = @umask();
    if(!$umask) $umask = 0000;

    // check what is set automatically by the system on file creation
    // and set the fperm param if it's not what we want
    $auto_fmode = 0666 & ~$umask;
    if($auto_fmode != $conf['fmode']) $conf['fperm'] = $conf['fmode'];

    // check what is set automatically by the system on file creation
    // and set the dperm param if it's not what we want
    $auto_dmode = $conf['dmode'] & ~$umask;
    if($auto_dmode != $conf['dmode']) $conf['dperm'] = $conf['dmode'];
}

/**
 * remove magic quotes recursivly
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function remove_magic_quotes(&$array) {
    foreach (array_keys($array) as $key) {
        // handle magic quotes in keynames (breaks order)
        $sk = stripslashes($key);
        if($sk != $key){
            $array[$sk] = $array[$key];
            unset($array[$key]);
            $key = $sk;
        }

        // do recursion if needed
        if (is_array($array[$key])) {
            remove_magic_quotes($array[$key]);
        }else {
            $array[$key] = stripslashes($array[$key]);
        }
    }
}

/**
 * Returns the full absolute URL to the directory where
 * DokuWiki is installed in (includes a trailing slash)
 *
 * !! Can not access $_SERVER values through $INPUT
 * !! here as this function is called before $INPUT is
 * !! initialized.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getBaseURL($abs=null){
    global $conf;
    //if canonical url enabled always return absolute
    if(is_null($abs)) $abs = $conf['canonical'];

    if(!empty($conf['basedir'])){
        $dir = $conf['basedir'];
    }elseif(substr($_SERVER['SCRIPT_NAME'],-4) == '.php'){
        $dir = dirname($_SERVER['SCRIPT_NAME']);
    }elseif(substr($_SERVER['PHP_SELF'],-4) == '.php'){
        $dir = dirname($_SERVER['PHP_SELF']);
    }elseif($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']){
        $dir = preg_replace ('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',
                $_SERVER['SCRIPT_FILENAME']);
        $dir = dirname('/'.$dir);
    }else{
        $dir = '.'; //probably wrong
    }

    $dir = str_replace('\\','/',$dir);             // bugfix for weird WIN behaviour
    $dir = preg_replace('#//+#','/',"/$dir/");     // ensure leading and trailing slashes

    //handle script in lib/exe dir
    $dir = preg_replace('!lib/exe/$!','',$dir);

    //handle script in lib/plugins dir
    $dir = preg_replace('!lib/plugins/.*$!','',$dir);

    //finish here for relative URLs
    if(!$abs) return $dir;

    //use config option if available, trim any slash from end of baseurl to avoid multiple consecutive slashes in the path
    if(!empty($conf['baseurl'])) return rtrim($conf['baseurl'],'/').$dir;

    //split hostheader into host and port
    if(isset($_SERVER['HTTP_HOST'])){
        $parsed_host = parse_url('http://'.$_SERVER['HTTP_HOST']);
        $host = isset($parsed_host['host']) ? $parsed_host['host'] : null;
        $port = isset($parsed_host['port']) ? $parsed_host['port'] : null;
    }elseif(isset($_SERVER['SERVER_NAME'])){
        $parsed_host = parse_url('http://'.$_SERVER['SERVER_NAME']);
        $host = isset($parsed_host['host']) ? $parsed_host['host'] : null;
        $port = isset($parsed_host['port']) ? $parsed_host['port'] : null;
    }else{
        $host = php_uname('n');
        $port = '';
    }

    if(is_null($port)){
        $port = '';
    }

    if(!is_ssl()){
        $proto = 'http://';
        if ($port == '80') {
            $port = '';
        }
    }else{
        $proto = 'https://';
        if ($port == '443') {
            $port = '';
        }
    }

    if($port !== '') $port = ':'.$port;

    return $proto.$host.$port.$dir;
}

/**
 * Check if accessed via HTTPS
 *
 * Apache leaves ,$_SERVER['HTTPS'] empty when not available, IIS sets it to 'off'.
 * 'false' and 'disabled' are just guessing
 *
 * @returns bool true when SSL is active
 */
function is_ssl(){
    // check if we are behind a reverse proxy
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
	    return true;
	} else {
	    return false;
	}
    }
    if (!isset($_SERVER['HTTPS']) ||
        preg_match('/^(|off|false|disabled)$/i',$_SERVER['HTTPS'])){
        return false;
    }else{
        return true;
    }
}

/**
 * print a nice message even if no styles are loaded yet.
 */
function nice_die($msg){
    echo<<<EOT
<!DOCTYPE html>
<html>
<head><title>DokuWiki Setup Error</title></head>
<body style="font-family: Arial, sans-serif">
    <div style="width:60%; margin: auto; background-color: #fcc;
                border: 1px solid #faa; padding: 0.5em 1em;">
        <h1 style="font-size: 120%">DokuWiki Setup Error</h1>
        <p>$msg</p>
    </div>
</body>
</html>
EOT;
    exit(1);
}

/**
 * A realpath() replacement
 *
 * This function behaves similar to PHP's realpath() but does not resolve
 * symlinks or accesses upper directories
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author <richpageau at yahoo dot co dot uk>
 * @link   http://php.net/manual/en/function.realpath.php#75992
 */
function fullpath($path,$exists=false){
    static $run = 0;
    $root  = '';
    $iswin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || @$GLOBALS['DOKU_UNITTEST_ASSUME_WINDOWS']);

    // find the (indestructable) root of the path - keeps windows stuff intact
    if($path{0} == '/'){
        $root = '/';
    }elseif($iswin){
        // match drive letter and UNC paths
        if(preg_match('!^([a-zA-z]:)(.*)!',$path,$match)){
            $root = $match[1].'/';
            $path = $match[2];
        }else if(preg_match('!^(\\\\\\\\[^\\\\/]+\\\\[^\\\\/]+[\\\\/])(.*)!',$path,$match)){
            $root = $match[1];
            $path = $match[2];
        }
    }
    $path = str_replace('\\','/',$path);

    // if the given path wasn't absolute already, prepend the script path and retry
    if(!$root){
        $base = dirname($_SERVER['SCRIPT_FILENAME']);
        $path = $base.'/'.$path;
        if($run == 0){ // avoid endless recursion when base isn't absolute for some reason
            $run++;
            return fullpath($path,$exists);
        }
    }
    $run = 0;

    // canonicalize
    $path=explode('/', $path);
    $newpath=array();
    foreach($path as $p) {
        if ($p === '' || $p === '.') continue;
        if ($p==='..') {
            array_pop($newpath);
            continue;
        }
        array_push($newpath, $p);
    }
    $finalpath = $root.implode('/', $newpath);

    // check for existence when needed (except when unit testing)
    if($exists && !defined('DOKU_UNITTEST') && !file_exists($finalpath)) {
        return false;
    }
    return $finalpath;
}

