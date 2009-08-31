<?php

require_once("PHPUnit/Framework.php");

class AllTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new AllTests;
        $suite->addTestFile('EntityTests.php');
        $suite->addTestFile('ModelTests.php');
        return $suite;
    }
}

