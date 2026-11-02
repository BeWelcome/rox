<?php

namespace App\Tests\Controller;

use App\Entity\Feedback;
use App\Entity\FeedbackCategory;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Feedback a member sends is stored in English and listed on the volunteers' check feedback page.
 */
#[Group('integration')]
class FeedbackTest extends WebTestCase
{
    public function testSentFeedbackIsStoredAndListedForTheVolunteers(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $category = $entityManager->getRepository(FeedbackCategory::class)->findOneBy(['visible' => 1]);

        $client->loginUser($member);
        $crawler = $client->request('GET', '/feedback');
        $client->submit($crawler->filter('form')->form(), [
            'IdCategory' => $category->getId(),
            'FeedbackQuestion' => '<p>zztest feedback question</p>',
            'no_reply_needed' => true,
        ]);

        $this->assertResponseRedirects('/feedback/received');
        $feedback = $entityManager->getRepository(Feedback::class)->findOneBy(['author' => $member], ['id' => 'DESC']);
        self::assertSame('<p>zztest feedback question</p>', $feedback->getDiscussion());
        self::assertSame('en', $feedback->getLanguage()->getShortCode());
        self::assertSame($category->getId(), $feedback->getCategory()->getId());

        $this->loginChecker($client);
        $client->request('GET', '/admin/tools/check/feedback');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'zztest feedback question');
    }

    /**
     * The hint to report a member opens by itself through the micromodal script (assets/js/micromodal.js), not
     * through an inline script that called a MicroModal the page doesn't have; a plain visit has no hint.
     */
    public function testTheReportHintOpensOnlyWhenThereIsOne(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $client->loginUser($entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']));
        $safety = $entityManager->getRepository(FeedbackCategory::class)->findOneBy(['name' => 'safety_and_abuse']);

        $crawler = $client->request('GET', '/feedback');
        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[data-micromodal-open]'));
        self::assertStringNotContainsString('MicroModal.show', (string) $client->getResponse()->getContent());

        $crawler = $client->request('GET', '/feedback?IdCategory=' . $safety->getId() . '&username=member-3');
        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#report-modal[data-micromodal-open]'));
        self::assertStringNotContainsString('MicroModal.show', (string) $client->getResponse()->getContent());
    }

    /**
     * Sending a report without adding to the prefilled text keeps the member on the page with one error at the
     * message, not the same error twice.
     */
    public function testAnUnchangedReportShowsItsErrorOnce(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $client->loginUser($entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']));
        $safety = $entityManager->getRepository(FeedbackCategory::class)->findOneBy(['name' => 'safety_and_abuse']);

        $crawler = $client->request('GET', '/feedback?IdCategory=' . $safety->getId() . '&username=member-3');
        $crawler = $client->submit($crawler->selectButton('submit')->form());

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.form-error-message'));
        self::assertCount(1, $crawler->filter('label[for="FeedbackQuestion"] .form-error-message'));
    }

    private function loginChecker(KernelBrowser $client): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        static::getContainer()->get(Connection::class)->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT ?, r.id, 10, '\"All\"', 'test', NOW(), NOW() FROM rights r
             WHERE r.Name = 'Checker' AND NOT EXISTS (
                 SELECT 1 FROM rightsvolunteers rv WHERE rv.IdMember = ? AND rv.IdRight = r.id
             )",
            [$admin->getId(), $admin->getId()]
        );
        $entityManager->refresh($admin);
        $client->loginUser($admin);
    }
}
