<?php
/*
Copyright (c) 2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/

    /**
     * @package    Tests
     * @subpackage OtherTests
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */

    /**
     * tests the Trip model class
     *
     * @package    Tests
     * @subpackage OtherTests
     * @author     Fake51
     */

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class EmailToolTest extends PHPUnit_Framework_TestCase
{
    public function testBadConstruct()
    {
        $this->setExpectedException('Exception');
        $email = new EmailTemplate('');
    }

    public function testGoodConstruct()
    {
        $email = new EmailTemplate('SignupAccepted');
    }

    public function testBadInit()
    {
        $email = new EmailTemplate('SignupAccepted');
        $this->assertFalse($email->init(array()));
    }

    public function testGoodInit()
    {
        $ef = new RoxEntityFactory;
        $member = $ef->create('Member')->findById(1);
        $email = new EmailTemplate('SignupAccepted');
        $this->assertTrue($email->init(array('member' => $member)));
    }

    public function testGetSubject1()
    {
        $ef = new RoxEntityFactory;
        $member = $ef->create('Member')->findById(1);
        $email = new EmailTemplate('SignupAccepted');
        $email->init(array('member' => $member));
        $result = $email->getSubject();
        $this->assertTrue(is_string($result));
        $this->assertTrue(strlen($result) > 0);
    }

    public function testGetSubject2()
    {
        $email = new EmailTemplate('SignupAccepted');
        $result = $email->getSubject();
        $this->assertTrue(empty($result));
    }

    public function testGetSender1()
    {
        $ef = new RoxEntityFactory;
        $member = $ef->create('Member')->findById(1);
        $email = new EmailTemplate('SignupAccepted');
        $email->init(array('member' => $member));
        $result = $email->getSender();
        $this->assertTrue(is_string($result));
        $this->assertTrue(strlen($result) > 0);
    }

    public function testGetSender2()
    {
        $email = new EmailTemplate('SignupAccepted');
        $result = $email->getSender();
        $this->assertTrue(empty($result));
    }

    public function testGetReceiver1()
    {
        $ef = new RoxEntityFactory;
        $member = $ef->create('Member')->findById(1);
        $email = new EmailTemplate('SignupAccepted');
        $email->init(array('member' => $member));
        $result = $email->getReceiver();
        $this->assertTrue(is_string($result));
        $this->assertTrue(strlen($result) > 0);
    }

    public function testGetReceiver2()
    {
        $email = new EmailTemplate('SignupAccepted');
        $result = $email->getReceiver();
        $this->assertTrue(empty($result));
    }

    public function testGetEmailBody1()
    {
        $ef = new RoxEntityFactory;
        $member = $ef->create('Member')->findById(1);
        $email = new EmailTemplate('SignupAccepted');
        $email->init(array('member' => $member));
        $result = $email->getEmailBody();
        $this->assertTrue(is_string($result));
        $this->assertTrue(strlen($result) > 0);
    }

    public function testGetEmailBody2()
    {
        $email = new EmailTemplate('SignupAccepted');
        $result = $email->getEmailBody();
        $this->assertTrue(empty($result));
    }

    public function testSend1()
    {
        $email = new EmailTemplate('SignupAccepted');
        $this->assertFalse($email->send());
    }

    public function testSend2()
    {
        $email = new EmailTemplate('SignupAccepted');
        $email->init(array());
        $this->assertFalse($email->send());
    }

    public function testSend3()
    {
        $ef = new RoxEntityFactory;
        $member = $ef->create('Member')->findById(1);
        $email = new EmailTemplate('SignupAccepted');
        $email->init(array('member' => $member));
        $this->assertTrue($email->send());
    }
}
