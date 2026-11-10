<?php

namespace App\Tests\Pagerfanta;

use App\Entity\Log;
use App\Pagerfanta\CappedCountAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The logs are counted up to a limit: more entries give the limit, and the adapter tells they are more.
 */
#[Group('integration')]
final class CappedCountAdapterTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->getConnection()->beginTransaction();
        foreach (['first', 'second', 'third'] as $message) {
            $log = new Log();
            $log->setType('Debug');
            $log->setLogMessage("zztest capped {$message}");
            $this->entityManager->persist($log);
        }
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        $this->entityManager->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testMoreEntriesThanTheLimitGiveTheLimit(): void
    {
        $adapter = new CappedCountAdapter($this->logs(), 2);

        self::assertSame(2, $adapter->getNbResults());
        self::assertTrue($adapter->isCapped());
    }

    public function testFewerEntriesAreCountedExactly(): void
    {
        $adapter = new CappedCountAdapter($this->logs(), 3);

        self::assertSame(3, $adapter->getNbResults());
        self::assertFalse($adapter->isCapped());
    }

    public function testASliceHasTheEntitiesInOrder(): void
    {
        $adapter = new CappedCountAdapter($this->logs(), 2);

        $slice = $adapter->getSlice(1, 2);

        self::assertSame(
            ['zztest capped second', 'zztest capped first'],
            array_map(static fn (Log $log) => $log->getLogMessage(), [...$slice])
        );
    }

    private function logs(): QueryBuilder
    {
        // newest first as on the logs page; all three have the same second, so the id decides
        return $this->entityManager->getRepository(Log::class)->createQueryBuilder('l')
            ->andWhere('l.logMessage LIKE :message')
            ->setParameter('message', 'zztest capped %')
            ->orderBy('l.created', 'DESC')
            ->addOrderBy('l.id', 'DESC');
    }
}
