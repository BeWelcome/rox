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
        Fixtures::load(__DIR__.'/languages.yml', $manager);
        Fixtures::load(__DIR__.'/words.yml', $manager);
        Fixtures::load(__DIR__.'/countries.yml', $manager);
        Fixtures::load(__DIR__.'/cities.yml', $manager);
        Fixtures::load(__DIR__.'/members.yml', $manager);
        Fixtures::load(__DIR__.'/communitynews.yml', $manager);
    }
}
