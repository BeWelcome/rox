<?php

use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotEnv = new Dotenv('.');

$dotEnv->load();
