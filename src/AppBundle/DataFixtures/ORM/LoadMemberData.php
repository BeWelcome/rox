<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

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
