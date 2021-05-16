<?php
/* get images for different types
 *
 * Example url: /tools/testenv/images/?group=max&avatar=100&gallery=33
 * Or from cli: php index.php --group=max --avatar=100 --gallery=33
 * In both cases each parameter is optional (but nothing is done when all are omitted)
 * 'max' will create the real number of images based on db.
 * Any number limits the amount of images (excl. thumbnails) to that number
 *
 * 'max' is the most realistic scenario but this could take up a serious amount of
 * time (several hours, depending on your system) and diskspace (3 GB), so be aware
 * before you do so. Only groups are not so many images
 * For avatar and gallery the last created id is stored in a little file: status.csv
 * That will serve as a starting point for any new calls, in case of a data-update or if
 * you want to do it in stages.
 **/

use App\Kernel;

set_time_limit(0);
ini_set('memory_limit', '256M');

/* Make sure we start in the subdirectory */

$loader = require 'config/bootstrap.php';

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$cwd = getcwd();
$parts = $chars = preg_split('^/^', $cwd, -1, PREG_SPLIT_NO_EMPTY);
if ($parts[count($parts) - 1] != 'images') {
    chdir('tools/testenv/images');
}

$dbHost = $container->getParameter('database_host');
$dbName = $container->getParameter('database_name');
$dbUser = $container->getParameter('database_user');
$dbPassword = $container->getParameter('database_password');

$dbController = new DatabaseController($dbHost, $dbName, $dbUser, $dbPassword);

// $group = new GroupImagesCreator($dbController);
// $group->getImages();

$avatar = new AvatarImagesCreator($dbController);
$avatar->getImages();

// $gallery = new GalleryImagesCreator($dbController);
// $gallery->getImages();
