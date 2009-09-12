<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class FeedbacksModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new FeedbackModel;
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testFeedbackMail()
    {
        $model = new FeedbackModel;

        $this->assertFalse($model->feedbackMail(false, false, false, false));
    }

}
