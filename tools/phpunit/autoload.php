<?php
// Load trips model manually

define('SCRIPT_BASE', dirname(__FILE__) . "/../../");

require_once SCRIPT_BASE . 'vendor/autoload.php';
require_once SCRIPT_BASE . 'roxlauncher/roxloader.php';
require_once SCRIPT_BASE . 'roxlauncher/environmentexplorer.php';

// load Rox environment
$env_explorer = new EnvironmentExplorer;
$env_explorer->initializeGlobalState();
