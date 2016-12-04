<?php

namespace AppBundle\TranslationLoader;

use AppBundle\Entity\Word;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function getEmMock()
    {
        $mockRep = \Mockery::mock('\Doctrine\ORM\EntityRepository');
        $mockRep->shouldReceive('findBy')->andReturn([ new Word() ]);
        $mockEm = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $mockEm->shouldReceive('getRepository')->andReturn($mockRep);
        return $mockEm;
    }

    public function test()
    {
        $mockEm = $this->getEmMock();

        $loader = new DatabaseLoader( $mockEm );

        $result = $loader->load(null, 'en');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
    }
}
