<?php

define('SCRIPT_BASE', realpath('../') . '/');

require_once SCRIPT_BASE . 'roxlauncher/roxlauncher.php';
require_once SCRIPT_BASE . 'roxlauncher/environmentexplorer.php';

$launcher = new EnvironmentExplorer;
$launcher->initializeGlobalState();

