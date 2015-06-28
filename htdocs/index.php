<?php
require '../vendor/autoload.php';

function main() {

    if (!version_compare(phpversion(), '5.1.0', '>=')) {
        die('Only for PHP version 5.1.0 or greater!');
    }
    
    // Find out whether the scripts reside in a subdir or not
    // Use base.xml to mark the SCRIPT_BASE directory.
    $script_base = dirname(__FILE__) . "/..";
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
    // error_reporting(E_ALL);
    // for php5.4x uncomment the below and comment out the above
    error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);

    try {
        require_once SCRIPT_BASE.'roxlauncher/roxlauncher.php';
        $launcher = new RoxLauncher();
        $launcher->launch();
    } catch (PException $e) {
        // XML header is a bad idea in this case,
        // because most likely the application already started with XHTML
        // header('Content-type: application/xml; charset=utf-8');
        echo '<pre>'; print_r($e); echo '</pre>';
        exit();
    } catch (Exception $e) {
        echo 'Exception: '.$e->getMessage();
        echo "\n{$e->getFile()} ({$e->getLine()})";
        exit();
    }

    session_write_close();
}

main();
