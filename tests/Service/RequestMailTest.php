<?php

namespace App\Tests\Service;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Service\Mailer;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mime\Email;

/**
 * The mail about a hosting request (emails/request.html.twig on emails/base.html.twig): who would like to stay with
 * their avatar, the stay with its dates and nights, the message, and the actions to reply, see the profile and
 * report it; the footer links to the host's mail settings.
 */
#[Group('integration')]
class RequestMailTest extends KernelTestCase
{
    public function testTheHostGetsTheRequestAsAStayCard(): void
    {
        self::bootKernel();
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-2']);
        $host = $members->findOneBy(['username' => 'member-3']);

        $request = new HostingRequest()
            ->setArrival(new Carbon('2026-11-12'))
            ->setDeparture(new Carbon('2026-11-14'))
            ->setNumberOfTravellers(1)
            ->setFlexible(true)
            ->setStatus(HostingRequest::REQUEST_DECLINED);
        $message = new Message()
            ->setMessage('<p>Could you host me? <script>alert(1)</script></p>')
            ->setSubject(new Subject()->setSubject('Stay in November'))
            ->setRequest($request)
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setCreated(new Carbon('2026-11-01 09:41'));
        new ReflectionProperty(Message::class, 'id')->setValue($message, 4242);

        static::getContainer()->get(Mailer::class)->sendMessageNotificationEmail($guest, $host, 'request', [
            'host' => $host,
            'subject' => 'Stay in November',
            'message' => $message,
            'request' => $request,
            'changed' => false,
        ]);

        $mail = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $mail);
        $html = (string) $mail->getHtmlBody();
        $crawler = new Crawler($html);

        // the layout: the card, the footer with the host's settings
        self::assertCount(1, $crawler->filter('td.bw-card'));
        self::assertCount(1, $crawler->filter('a[href$="/members/member-3/preferences"]'));
        // who: the avatar from the site
        // the avatar, signed for the mail program
        self::assertCount(1, $crawler->filter('img[src*="/members/avatar/member-2/96?"][src*="_hash="]'));
        self::assertCount(1, $crawler->filter('img[alt="member-2"]'));
        // the stay: 2 nights, struck through as it was declined
        self::assertStringContainsString('12 Nov', $html);
        self::assertStringContainsString('14 Nov', $html);
        self::assertStringContainsString('line-through', $html);
        // the message, purified
        self::assertStringContainsString('Could you host me?', $html);
        self::assertStringNotContainsString('<script', $html);
        // the actions
        self::assertCount(1, $crawler->filter('a[href$="/conversation/4242/reply"]'));
        self::assertCount(1, $crawler->filter('a[href$="/members/member-2"]'));
        self::assertCount(1, $crawler->filter('a[href$="/conversation/4242/report"]'));
        // well under the size at which Gmail cuts a mail off (about 100 KB)
        self::assertLessThan(60_000, \strlen($html), 'size: ' . \strlen($html));
    }
}
