<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/xml.php');

require_once('../lib/misc/functions.lib.php');


class ValidationTests extends UnitTestCase {
  
  function testEmailValidation() {
    $this->assertTrue(PFunctions::isEmailAddress("kasper.souren@gmail.com"));
    $this->assertFalse(PFunctions::isEmailAddress("kasper.souren.gmail.com"));
  }
}



$test = &new ValidationTests();
// $test->run(new HtmlReporter());
$test->run(new XmlReporter());
?>
