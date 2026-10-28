<?php

namespace App\Tests\Controller;

use App\Entity\BroadcastMessage;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Preference;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The unsubscribe link of a newsletter, as members use it: the link in the mail shows a confirmation page, and the
 * one-click POST of mail clients (RFC 8058) unsubscribes without one. The routes mapped the username to a Member,
 * which the controller doesn't take, so both failed with a 500.
 */
#[Group('integration')]
class NewsletterUnsubscribeTest extends WebTestCase
{
    private const string KEY = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $entityManager = $this->entityManager();
        $members = $entityManager->getRepository(Member::class);
        $newsletter = new Newsletter();
        $newsletter->setName('zztest_unsubscribe');
        $newsletter->setType(Newsletter::REGULAR_NEWSLETTER);
        $newsletter->setCreatedBy($members->findOneBy(['username' => 'bwadmin']));
        $newsletter->setCreated(new DateTime());
        $entityManager->persist($newsletter);
        $message = new BroadcastMessage();
        $message->setNewsletter($newsletter)
            ->setEnqueuedBy($members->findOneBy(['username' => 'bwadmin']))
            ->setStatus('Sent')
            ->setUpdated(new DateTime())
            ->setUnsubscribeKey(self::KEY);
        $message->setReceiver($members->findOneBy(['username' => 'member-2']));
        $entityManager->persist($message);
        $entityManager->flush();
    }

    public function testTheLinkInTheMailUnsubscribesAfterTheConfirmation(): void
    {
        $crawler = $this->client->request('GET', '/unsubscribe/newsletter/member-2/' . self::KEY);
        $this->assertResponseIsSuccessful();

        $this->client->submit($crawler->filter("button[name$='[confirm]']")->form());

        $this->assertResponseIsSuccessful();
        self::assertSame('No', $this->newsletterPreference());
    }

    public function testMailClientsUnsubscribeWithOneClick(): void
    {
        $this->client->request('POST', '/unsubscribe/newsletter/member-2/' . self::KEY, ['List-Unsubscribe' => 'One-Click']);

        $this->assertResponseIsSuccessful();
        self::assertSame('', $this->client->getResponse()->getContent());
        self::assertSame('No', $this->newsletterPreference());
    }

    public function testTheLocalEventsLinkShowsTheConfirmation(): void
    {
        $this->client->request('GET', '/unsubscribe/local/member-2/' . self::KEY);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists("button[name$='[confirm]']");
    }

    private function newsletterPreference(): string
    {
        $entityManager = $this->entityManager();
        $entityManager->clear();
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $preference = $entityManager->getRepository(Preference::class)->findOneBy(['codename' => Preference::NEWSLETTERS_VIA_EMAIL]);

        return $member->getMemberPreference($preference)->getValue();
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
