<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class GalleryModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new GalleryModel;
        $this->assertTrue($model instanceof GalleryModel);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testGetGalleryBad()
    {
        $model = new GalleryModel;
        $this->assertFalse($model->getGallery(0));
    }

    public function testGetGalleryGood()
    {
        $model = new GalleryModel;
        $gallery = $model->getGallery(1);
        $this->assertTrue($gallery instanceof Gallery);
        $this->assertTrue($gallery->isLoaded());
    }
}
