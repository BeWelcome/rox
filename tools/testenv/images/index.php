<?php
set_time_limit(0);
ini_set('memory_limit', '256M');

require 'autoload.php';
spl_autoload_register('main');

$group = new GroupImagesCreator();
$avatar = new AvatarImagesCreator();
$gallery = new GalleryImagesCreator();


/* get images for different types
 * optional argument: maximum number of images (excl. thumbnails) to be generated
 * by default, the number of created items is maximized based on the real numbers from db.
 * That is the most realistic scenario but this could take up a serious amount of
 * time (several hours, depending on your system) and diskspace (3 GB)
 * only for groups there is generally no need to limit */

$group->getImages(1);
$avatar->getImages(1);
$gallery->getImages(1);
