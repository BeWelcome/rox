<?php

use Dotenv\Dotenv;

require 'vendor/autoload.php';

if (class_exists(Dotenv::class) && file_exists('.env')) {
    $dotEnv = new Dotenv('.');

    $dotEnv->load();
}
