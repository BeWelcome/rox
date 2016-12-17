<?php

namespace AppBundle\DataFixtures\ORM;

use Nelmio\Alice\Fixtures;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;


class LoadMemberData implements FixtureInterface
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function load(ObjectManager $manager)
    {
        $objects = Fixtures::load(__DIR__.'/languages.yml', $manager);
        $objects = Fixtures::load(__DIR__.'/words.yml', $manager);
    }
}