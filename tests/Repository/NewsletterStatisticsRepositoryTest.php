<?php

namespace App\Tests\Repository;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\NewsletterStatistics;
use App\Repository\NewsletterStatisticsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
class NewsletterStatisticsRepositoryTest extends KernelTestCase
{
    public function testTheCountsAreChangedAndNeverNegative(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $newsletter = new Newsletter();
        $newsletter->setName('zztest_statistics')->setType(Newsletter::REGULAR_NEWSLETTER)->setStatus('Created')
            ->setCreated(new DateTime())
            ->setCreatedBy($entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']));
        $entityManager->persist($newsletter);
        $entityManager->flush();
        $repository = static::getContainer()->get(NewsletterStatisticsRepository::class);

        $repository->add($newsletter, enqueued: 5);
        $repository->add($newsletter, enqueued: -5, triggered: 5);
        $repository->add($newsletter->getId(), triggered: -3, sent: 2, failed: 1);
        $repository->add($newsletter, triggered: -10);

        $entityManager->clear();
        /** @var NewsletterStatistics $statistics */
        $statistics = $repository->find($newsletter->getId());
        self::assertSame(0, $statistics->getEnqueued());
        self::assertSame(0, $statistics->getTriggered());
        self::assertSame(2, $statistics->getSent());
        self::assertSame(1, $statistics->getFailed());
    }
}
