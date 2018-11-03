<?php

namespace App\TranslationLoader;

use App\Entity\Word;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DatabaseLoaderTest extends TestCase
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
