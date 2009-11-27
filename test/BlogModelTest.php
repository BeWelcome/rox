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
     * @subpackage ModelTests
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */

    /**
     * tests the Blog model class
     *
     * @package    Tests
     * @subpackage ModelTests
     * @author     Fake51
     */

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class BlogModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new Blog;
        $this->assertTrue($model instanceof Blog);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testCountRecentPosts()
    {
        $model = new Blog;

        $this->assertTrue(is_numeric($model->countRecentPosts()));
        $this->assertTrue(is_numeric($model->countRecentPosts(74)));
        $this->assertTrue(is_numeric($model->countRecentPosts('fake51')));
        $this->assertTrue($model->countRecentPosts('fake51') == 0);
    }

    public function testGetRecentPostsArray()
    {
        $model = new Blog;

        $this->assertTrue(is_array($model->getRecentPostsArray()));
        $this->assertTrue(is_array($model->getRecentPostsArray(74)));
        $this->assertTrue(is_array($model->getRecentPostsArray('fake51')));
        $this->assertTrue(count($model->getRecentPostsArray('fake51')) == 0);
        $this->assertTrue(is_array($model->getRecentPostsArray(false, false, 2)));
        $this->assertTrue(is_array($model->getRecentPostsArray(74, false, 2)));
    }

    public function testGetMemberByUsername()
    {
        $model = new Blog;

        $member = $model->getMemberByUsername('fake51');
        $this->assertTrue(is_object($member));
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
    }

    public function testGetCategoryArray()
    {
        $model = new Blog;

        $this->assertTrue(is_array($model->getCategoryArray()));
        $this->assertTrue(is_array($model->getRecentPostsArray(11)));
        $this->assertTrue(is_array($model->getRecentPostsArray('fake51')));
        $this->assertTrue(count($model->getRecentPostsArray('fake51')) == 0);

        $member = $model->getMemberByUsername('fake51');
        $this->assertTrue(is_array($model->getRecentPostsArray(false, $member)));
        $this->assertTrue(is_array($model->getRecentPostsArray(11, $member)));
    }

    public function testSearchPosts()
    {
        $model = new Blog;

        $this->assertTrue(is_array($model->searchPosts('')));
        $this->assertTrue(is_array($model->searchPosts('henri')));
        $this->assertTrue(count($model->searchPosts('henri')) > 0);
    }

    public function testGetTripFromUserIt()
    {

    }

    public function testIsUserTrip()
    {

    }
}
