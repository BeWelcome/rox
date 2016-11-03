<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Dotenv\Dotenv;

require 'vendor/autoload.php';

if (class_exists(Dotenv::class) && file_exists('.env')) {
    $dotEnv = new Dotenv('.');

    $dotEnv->load();
}

$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);
