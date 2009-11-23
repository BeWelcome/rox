<?php

require_once("PHPUnit/Framework.php");

class EntityTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new EntityTests;
        $suite->addTestFile('BlogEntityTest.php');
        $suite->addTestFile('GroupEntityTest.php');
        $suite->addTestFile('GroupMembershipEntityTest.php');
        $suite->addTestFile('MemberEntityTest.php');
        $suite->addTestFile('VolunteerBoardEntityTest.php');
        return $suite;
    }
}
