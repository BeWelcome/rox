<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$rootDir = realpath( __DIR__.'/../' );
echo $rootDir;

$loader = require $rootDir . '/vendor/autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$kernel = new AppKernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
