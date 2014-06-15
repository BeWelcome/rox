<?php
/* get images for different types
 *
 * Example url: /tools/testenv/images/?group=max&avatar=100&gallery=33
 * 'max' will create the real number of images based on db.
 * Any number limits the amount of images (excl. thumbnails) to that number 
 *
 * 'max' is the most realistic scenario but this could take up a serious amount of
 * time (several hours, depending on your system) and diskspace (3 GB), so be aware
 * before you do so. Only groups are not so many images
 **/

set_time_limit(0);
ini_set('memory_limit', '256M');

require 'autoload.php';
spl_autoload_register('main');

$group = new GroupImagesCreator();
$avatar = new AvatarImagesCreator();
$gallery = new GalleryImagesCreator();

$group->getImages($group->getLimit());
$avatar->getImages($avatar->getLimit());
$gallery->getImages($gallery->getLimit());
