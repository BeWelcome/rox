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
    ->in('./')
;

// generate documentation for all v2.0.* tags, the 2.0 branch, and the master one
$versions = GitVersionCollection::create('./')
    ->add('bootstrap3', 'Bootstrap Redesign Branch')
;

return new Sami($iterator, array(
    'theme'                => 'symfony',
    'versions'             => $versions,
    'title'                => 'Rox',
    'build_dir'            => 'doc/build/bs4/%version%',
    'cache_dir'            => 'doc/cache/bs4/%version%',
    'default_opened_level' => 2,
));

