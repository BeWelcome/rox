<?php

namespace App\TranslationLoader;

use App\Entity\Word;
use App\Repository\WordRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DatabaseLoaderTest extends TestCase
{
    /**
     * @return
     */
    protected function getEmMock()
    {
        $mockRep = \Mockery::mock(WordRepository::class);
        $mockRep->shouldReceive('findBy')->andReturn([ new Word() ]);
        $mockEm = \Mockery::mock(EntityManager::class);
        $mockEm->shouldReceive('getRepository')->andReturn($mockRep);
        return $mockEm;
    }

    public function test()
    {
        $mockEm = $this->getEmMock();

        $loader = new DatabaseLoader($mockEm);

        $result = $loader->load(null, 'en');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
    }
}
