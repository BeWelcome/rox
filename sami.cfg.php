<?php

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('data')
    ->exclude('Tests')
    ->exclude('htdocs')
    ->exclude('vendor')
    ->in('./')
;

// generate documentation for all v2.0.* tags, the 2.0 branch, and the master one
$versions = GitVersionCollection::create('./')
    ->add('bootstrap3', 'Bootstrap Redesign Branch')
;

return new Sami($iterator, array(
    'theme'                => 'default',
    'versions'             => $versions,
    'title'                => 'Rox',
    'build_dir'            => 'doc/',
    'cache_dir'            => 'cache/bs4/%version%',
    'default_opened_level' => 2,
));

