<?php
require "vendor/autoload.php";

use Translation\Extractor\Extractor;
use Translation\Extractor\FileExtractor\PHPFileExtractor;
use Translation\Extractor\Visitor\Php\Symfony as Visitor;

// Create extractor for PHP files
$fileExtractor = new PHPFileExtractor();

// Add visitors
$fileExtractor->addVisitor(new Visitor\ContainerAwareTrans());
$fileExtractor->addVisitor(new Visitor\ContainerAwareTransChoice());
$fileExtractor->addVisitor(new Visitor\FlashMessage());
$fileExtractor->addVisitor(new Visitor\FormTypeChoices());

// Add the file extractor to Extractor
$extractor = new Extractor();
$extractor->addFileExtractor($fileExtractor);

//Start extracting files
$sourceCollection = $extractor->extractFromDirectory('templates/');

// Print the result
foreach ($sourceCollection as $source) {
    echo sprintf('Key "%s" found in %s at line %d', $source->getMessage(), $source->getPath(), $source->getLine());
}