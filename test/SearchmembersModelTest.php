<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class SearchmembersModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new Searchmembers;
        $this->assertTrue($model instanceof Searchmembers);
        $this->assertTrue($model instanceof RoxModelBase);
    }

}
