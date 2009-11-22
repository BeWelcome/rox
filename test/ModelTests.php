<?php

require_once("PHPUnit/Framework.php");

class ModelTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new ModelTests;
        $suite->addTestFile('AdminModelTest.php');
        $suite->addTestFile('FeedbackModelTest.php');
        $suite->addTestFile('ForumsModelTest.php');
        $suite->addTestFile('BlogModelTest.php');
        $suite->addTestFile('TripModelTest.php');
        $suite->addTestFile('GroupsModelTest.php');
        $suite->addTestFile('GalleryModelTest.php');
        $suite->addTestFile('SearchmembersModelTest.php');
        return $suite;
    }
}
