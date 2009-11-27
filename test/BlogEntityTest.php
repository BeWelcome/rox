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
     * @subpackage EntityTests
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */

    /**
     * tests the Blog entity class
     *
     * @package    Tests
     * @subpackage EntityTests
     * @author     Fake51
     */

require_once("PHPUnit/Framework.php");

class BlogEntityTest extends PHPUnit_Framework_TestCase
{
    protected $entity_factory;

    public function setUp()
    {
        require_once 'core_includes.php';
        $this->entity_factory = new RoxEntityFactory;
    }

    public function testCreation()
    {
        $blog = $this->entity_factory->create('BlogEntity');
        $this->assertEquals(true, $blog instanceof BlogEntity);
        $this->assertEquals(true, $blog instanceof RoxEntityBase);
    }


    public function testFindBlogGood()
    {
        $blog = $this->entity_factory->create('BlogEntity')->findById(2);
        $this->assertEquals(true, $blog instanceof RoxEntityBase);
    }

    public function testFindBlogBad()
    {
        $blog = $this->entity_factory->create('BlogEntity')->findById(1000000000);
        $this->assertEquals(false, $blog);

        $blog = $this->entity_factory->create('BlogEntity')->findById(-1);
        $this->assertEquals(false, $blog);

        $blog = $this->entity_factory->create('BlogEntity')->findById('here');
        $this->assertEquals(false, $blog);
    }

    public function testLoaded()
    {
        $blog = $this->entity_factory->create('BlogEntity');
        $this->assertEquals(true, $blog instanceof BlogEntity);
        $this->assertEquals(false, $blog->isLoaded());
        $new_blog = $this->entity_factory->create('BlogEntity')->findById(2);
        $this->assertEquals(true, $blog instanceof BlogEntity);
        $this->assertEquals(true, $new_blog->isLoaded());
    }

    public function testInsertAndDelete()
    {
        $blog = $this->entity_factory->create('BlogEntity');
        $this->assertEquals(true, $blog instanceof BlogEntity);
        $this->assertEquals(false, $blog->isLoaded());

        $date = date('Y-m-d H:i:s');
        $startdate = date('Y-m-d H:i:s', strtotime('+1 week'));
        $enddate = date('Y-m-d H:i:s', strtotime('+2 week'));
        $blog->IdMember = 1000;
        $blog->blog_created = $date;
        $blog->country_id_foreign = 100;
        $blog->trip_id_foreign = 200;

        $blog->blog_title = 'testtitle';
        $blog->blog_text = 'testtext';
        $blog->blog_start = $startdate;
        $blog->blog_end = $enddate;
        $blog->blog_latitude = 30;
        $blog->blog_longitude = 40;
        $blog->blog_geonameid = 2000;
        $blog->blog_display_order = 4000;

        $this->assertTrue($blog->insert());

        $newblog = $this->entity_factory->create('BlogEntity')->findById($blog->blog_id);
        $this->assertEquals(strtotime($date), strtotime($newblog->blog_created));
        $this->assertEquals(1000, $newblog->IdMember);
        $this->assertEquals(100, $newblog->country_id_foreign);
        $this->assertEquals(200, $newblog->trip_id_foreign);
        $this->assertEquals(strtotime($startdate), strtotime($newblog->blog_start));
        $this->assertEquals(strtotime($enddate), strtotime($newblog->blog_end));
        $this->assertEquals('testtitle', $newblog->blog_title);
        $this->assertEquals('testtext', $newblog->blog_text);
        $this->assertEquals(30, $newblog->blog_latitude);
        $this->assertEquals(40, $newblog->blog_longitude);
        $this->assertEquals(2000, $newblog->blog_geonameid);
        $this->assertEquals(4000, $newblog->blog_display_order);

        $this->assertTrue($newblog->delete());
    }

    public function testInsertAndUpdate()
    {
        $blog = $this->entity_factory->create('BlogEntity');
        $this->assertEquals(true, $blog instanceof BlogEntity);
        $this->assertEquals(false, $blog->isLoaded());

        $date = date('Y-m-d H:i:s');
        $startdate = date('Y-m-d H:i:s', strtotime('+1 week'));
        $enddate = date('Y-m-d H:i:s', strtotime('+2 week'));
        $blog->IdMember = 1000;
        $blog->blog_created = $date;
        $blog->country_id_foreign = 100;
        $blog->trip_id_foreign = 200;

        $blog->edited = $date;
        $blog->blog_title = 'testtitle';
        $blog->blog_text = 'testtext';
        $blog->blog_start = $startdate;
        $blog->blog_end = $enddate;
        $blog->blog_latitude = 30;
        $blog->blog_longitude = 40;
        $blog->blog_geonameid = 2000;
        $blog->blog_display_order = 4000;

        $this->assertTrue($blog->insert());

        $date = date('Y-m-d H:i:s', strtotime('-1 week'));
        $startdate = date('Y-m-d H:i:s', strtotime('+4 week'));
        $enddate = date('Y-m-d H:i:s', strtotime('+5 week'));
        $blog->IdMember = 2000;
        $blog->blog_created = $date;
        $blog->country_id_foreign = 500;
        $blog->trip_id_foreign = 600;

        $blog->edited = $date;
        $blog->blog_title = 'testtitlexxx';
        $blog->blog_text = 'testtextxxx';
        $blog->blog_start = $startdate;
        $blog->blog_end = $enddate;
        $blog->blog_latitude = 50;
        $blog->blog_longitude = 60;
        $blog->blog_geonameid = 7000;
        $blog->blog_display_order = 8000;

        $this->assertTrue($blog->update());

        $newblog = $this->entity_factory->create('BlogEntity')->findById($blog->blog_id);
        $this->assertEquals(strtotime($date), strtotime($newblog->blog_created));
        $this->assertEquals(2000, $newblog->IdMember);
        $this->assertEquals(500, $newblog->country_id_foreign);
        $this->assertEquals(600, $newblog->trip_id_foreign);
        $this->assertEquals(strtotime($startdate), strtotime($newblog->blog_start));
        $this->assertEquals(strtotime($enddate), strtotime($newblog->blog_end));
        $this->assertEquals('testtitlexxx', $newblog->blog_title);
        $this->assertEquals('testtextxxx', $newblog->blog_text);
        $this->assertEquals(50, $newblog->blog_latitude);
        $this->assertEquals(60, $newblog->blog_longitude);
        $this->assertEquals(7000, $newblog->blog_geonameid);
        $this->assertEquals(8000, $newblog->blog_display_order);

        $this->assertTrue($newblog->delete());
    }
}
