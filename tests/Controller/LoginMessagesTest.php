<?php

namespace App\Tests\Controller;

use App\Entity\LoginMessage;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Site announcements: a strip per message the member hasn't acknowledged, oldest first, under the top bar; "Got it"
 * acknowledges one (a POST with the token) and it no longer shows.
 */
#[Group('integration')]
final class LoginMessagesTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testTheStripsShowOldestFirstAndGoWhenAcknowledged(): void
    {
        $older = $this->message('<p><strong>zztest older</strong> text</p>', '-2 days');
        $newer = $this->message('<p>zztest newer<script>alert(1)</script></p>', '-1 day');
        $this->message('<p>zztest expired</p>', '-3 days', '-1 hour');
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/');
        $strips = $this->strips($crawler);
        self::assertSame(['zztest older text', 'zztest newer'], $strips->each(static fn (Crawler $strip): string => trim($strip->filter('.p-login-message__body')->text())));
        self::assertCount(0, $crawler->filter('.p-login-message__body script'), 'the message is purified');
        self::assertCount(1, $strips->eq(0)->filter('button[data-login-message-ack]'));
        $url = $strips->eq(0)->attr('data-login-message');
        self::assertSame('/loginmessage/acknowledge/' . $older->getId(), $url);
        $token = $crawler->filter('[data-login-messages]')->attr('data-token');

        // a GET, no token, someone else's session: nothing changes
        $this->client->request('GET', $url);
        self::assertResponseStatusCodeSame(405);
        $this->client->request('POST', $url);
        self::assertResponseStatusCodeSame(403);

        $this->client->request('POST', $url, ['_token' => $token]);
        self::assertResponseStatusCodeSame(204);

        $crawler = $this->client->request('GET', '/');
        self::assertSame(['zztest newer'], $this->strips($crawler)->each(static fn (Crawler $strip): string => trim($strip->filter('.p-login-message__body')->text())));
        self::assertSame('/loginmessage/acknowledge/' . $newer->getId(), $this->strips($crawler)->attr('data-login-message'));
    }

    public function testVisitorsCantAcknowledge(): void
    {
        $message = $this->message('<p>zztest</p>', '-1 day');

        $this->client->request('POST', '/loginmessage/acknowledge/' . $message->getId());

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    private function strips(Crawler $crawler): Crawler
    {
        return $crawler->filter('.p-login-message')->reduce(static fn (Crawler $strip): bool => str_contains($strip->text(), 'zztest'));
    }

    private function message(string $text, string $created, string $expires = '+1 day'): LoginMessage
    {
        $message = new LoginMessage()
            ->setMessage($text)
            ->setCreated(new DateTime($created))
            ->setExpires(new DateTime($expires));
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    private function member(string $username): Member
    {
        return $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}
