<?php

require_once("PHPUnit/Framework.php");

class EntityTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new EntityTests;
        $suite->addTestFile('GroupEntityTest.php');
        $suite->addTestFile('GroupMembershipEntityTest.php');
        $suite->addTestFile('MemberEntityTest.php');
        return $suite;
    }
}
