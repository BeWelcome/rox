<?php

namespace App\Tests\Controller;

use App\Entity\Feedback;
use App\Entity\FeedbackCategory;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Feedback a member sends is stored in English and mailed to the address of its category, where the volunteers read
 * it (the volunteer tools no longer list it).
 */
#[Group('integration')]
class FeedbackTest extends WebTestCase
{
    public function testSentFeedbackIsStoredAndMailedToItsCategory(): void
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

        $this->assertEmailCount(1);
        $mail = $this->getMailerMessage();
        $this->assertEmailAddressContains($mail, 'To', $category->getEmailtonotify());
        $this->assertEmailHtmlBodyContains($mail, 'zztest feedback question');
    }

    /**
     * A safety report shows the note under the topic instead of a dialog (it shows for that topic only, about/contact.js
     * keeps it in step with the choice); coming from a message, a dialog offers to report that message.
     */
    public function testASafetyReportShowsTheNoteAndAMessageTheDialog(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $client->loginUser($entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']));
        $safety = $entityManager->getRepository(FeedbackCategory::class)->findOneBy(['name' => 'safety_and_abuse']);

        $crawler = $client->request('GET', '/feedback');
        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[data-micromodal-open]'));
        self::assertCount(1, $crawler->filter('#contact-safety-note[hidden]'));
        self::assertSame((string) $safety->getId(), $crawler->filter('select.js-contact-topic')->attr('data-safety'));
        // members find their address filled in
        self::assertNotSame('', (string) $crawler->filter('input[name="FeedbackEmail"]')->attr('value'));

        $crawler = $client->request('GET', '/feedback?IdCategory=' . $safety->getId() . '&username=member-3');
        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[data-micromodal-open]'));
        self::assertCount(1, $crawler->filter('#contact-safety-note:not([hidden])'));

        $crawler = $client->request('GET', '/feedback?IdCategory=' . $safety->getId() . '&username=member-3&messageId=1');
        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#report-modal[data-micromodal-open] a[href$="/1/report"]'));
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
}
