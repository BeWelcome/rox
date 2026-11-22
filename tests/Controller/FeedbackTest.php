<?php

namespace App\Tests\Controller;

use App\Entity\FeedbackCategory;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Feedback a member sends is mailed to the address of its category, where the volunteers read it; it isn't stored,
 * nothing on the site shows it.
 */
#[Group('integration')]
class FeedbackTest extends WebTestCase
{
    public function testSentFeedbackIsMailedToItsCategory(): void
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

        $this->assertEmailCount(1);
        $mail = $this->getMailerMessage();
        $this->assertEmailAddressContains($mail, 'To', $category->getEmailtonotify());
        $this->assertEmailHtmlBodyContains($mail, 'zztest feedback question');
    }

    /**
     * A visitor proves to be human with the captcha: the form shows its image, a wrong code keeps the form with an error
     * and sends nothing, the code from the image sends the feedback.
     */
    public function testAVisitorSendsFeedbackWithTheCaptcha(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $category = $entityManager->getRepository(FeedbackCategory::class)->findOneBy(['visible' => 1]);
        $fields = [
            'IdCategory' => $category->getId(),
            'FeedbackQuestion' => '<p>zztest visitor feedback</p>',
            'FeedbackEmail' => 'visitor@example.com',
        ];

        $crawler = $client->request('GET', '/feedback');
        $image = $crawler->filter('img[src^="data:image/jpeg;base64,"]');
        self::assertCount(1, $image);
        $data = base64_decode(substr((string) $image->attr('src'), \strlen('data:image/jpeg;base64,')), true);
        self::assertNotFalse(@imagecreatefromstring((string) $data), 'the captcha is a readable image');

        $client->submit($crawler->filter('form')->form(), ['captcha' => 'wrong'] + $fields);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form [name="captcha"]');
        $this->assertEmailCount(0);

        $crawler = $client->request('GET', '/feedback');
        $phrase = $client->getRequest()->getSession()->get('_captcha_captcha')['phrase'];
        $client->submit($crawler->filter('form')->form(), ['captcha' => $phrase] + $fields);

        $this->assertResponseRedirects('/feedback/received');
        $this->assertEmailCount(1);
        $this->assertEmailHtmlBodyContains($this->getMailerMessage(), 'zztest visitor feedback');
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
        // under the field, not in its label (where it stood above the field)
        self::assertCount(0, $crawler->filter('label[for="FeedbackQuestion"] .form-error-message'));
        self::assertCount(1, $crawler->filter('label[for="FeedbackQuestion"] ~ .invalid-feedback .form-error-message'));
    }
}
