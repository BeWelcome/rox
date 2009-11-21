<?php

require_once("PHPUnit/Framework.php");

class ModelTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new ModelTests;
        $suite->addTestFile('ForumsModelTest.php');
        $suite->addTestFile('BlogModelTest.php');
        $suite->addTestFile('TripModelTest.php');
        $suite->addTestFile('GroupsModelTest.php');
        $suite->addTestFile('GalleryModelTest.php');
        $suite->addTestFile('SearchmembersModelTest.php');
        return $suite;
    }
}
