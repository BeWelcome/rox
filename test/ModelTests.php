<?php

require_once("PHPUnit/Framework.php");

class ModelTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new ModelTests;
        $suite->addTestFile('ForumsModelTest.php');
        $suite->addTestFile('BlogModelTest.php');
        return $suite;
    }
}
