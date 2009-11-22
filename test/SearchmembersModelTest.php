<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class SearchmembersModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new Searchmembers;
        $this->assertTrue($model instanceof Searchmembers);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testIsValidLang1()
    {
        $model = new Searchmembers;
        $this->assertTrue($model->isValidLang('en'));
    }

    public function testIsValidLang2()
    {
        $model = new Searchmembers;
        $this->assertFalse($model->isValidLang(''));
    }

    public function testGetLangNames()
    {
        $model = new Searchmembers;
        $this->assertTrue(is_array($model->getLangNames()));
    }

    public function testQuicksearch1()
    {
        $model = new Searchmembers;
        $result = $model->quicksearch('');
        $this->assertTrue(is_object($result));
        $this->assertTrue(is_array($result->TMembers));
        $this->assertTrue(empty($result->TMembers));
    }

    public function testQuicksearch2()
    {
        $model = new Searchmembers;
        $result = $model->quicksearch('Admin');
        $this->assertTrue(is_object($result));
        $this->assertTrue(is_array($result->TMembers));
        $this->assertTrue(empty($result->TMembers));
    }

    public function testQuicksearch3()
    {
        $model = new Searchmembers;
        $result = $model->quicksearch('Henri');
        $this->assertTrue(is_object($result));
        $this->assertTrue(is_array($result->TMembers));
        $this->assertFalse(empty($result->TMembers));
    }

    public function testSearch1()
    {
        $model = new Searchmembers;
        $vars = array();
        $this->assertTrue(is_array($model->search($vars)));
    }

    public function testSqlGetGroups()
    {
        $model = new Searchmembers;
        $result = $model->sql_get_groups();
        $this->assertTrue(is_array($result));
        foreach ($result as $group)
        {
            $this->assertTrue($group instanceof Group);
        }
    }

    public function testGetOrderDirection1()
    {
        $model = new Searchmembers;
        $result = $model->getOrderDirection('blahblah');
        $this->assertTrue(is_array($result));
        $this->assertTrue($result[0] == 'members.created');
        $this->assertTrue($result[1] == 'ASC');
    }

    public function testGetOrderDirection2()
    {
        $model = new Searchmembers;
        $result = $model->getOrderDirection('members.created');
        $this->assertTrue(is_array($result));
        $this->assertTrue($result[0] == 'members.created');
        $this->assertTrue($result[1] == 'DESC');
    }

    public function testGetOrderDirection3()
    {
        $model = new Searchmembers;
        $result = $model->getOrderDirection('members.created', 1);
        $this->assertTrue(is_array($result));
        $this->assertTrue($result[0] == 'members.created');
        $this->assertTrue($result[1] == 'ASC');
    }

    public function testGetOrderDirection4()
    {
        $model = new Searchmembers;
        $result = $model->getOrderDirection('members.created', 0);
        $this->assertTrue(is_array($result));
        $this->assertTrue($result[0] == 'members.created');
        $this->assertTrue($result[1] == 'DESC');
    }

    public function testGetDefaultSortDirection()
    {
        $model = new Searchmembers;
        $this->assertTrue(is_array($model->getDefaultSortDirection()));
    }

    public function testGetSortOrder()
    {
        $model = new Searchmembers;
        $this->assertTrue(is_array($model->get_sort_order()));
    }
}
