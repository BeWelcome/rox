<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The home page lists the member's accepted stays still to come, as guest and as host; declined ones and past
 * ones stay out. The stays calendar (/stays) shows those of a month.
 */
#[Group('integration')]
class UpcomingStaysTest extends WebTestCase
{
    public function testAcceptedStaysToComeAreListed(): void
    {
        $client = static::createClient();
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-2']);
        $host = $members->findOneBy(['username' => 'member-5']);

        $accepted = $this->request($guest, $host, HostingRequest::REQUEST_ACCEPTED, Carbon::today()->addDays(3));
        $declined = $this->request($guest, $host, HostingRequest::REQUEST_DECLINED, Carbon::today()->addDays(10));
        $past = $this->request($guest, $host, HostingRequest::REQUEST_ACCEPTED, Carbon::today()->subDays(20));

        $client->loginUser($guest);
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.c-home-stays a[href="/conversation/' . $accepted->getId() . '"]'));
        self::assertCount(0, $crawler->filter('.c-home-stays a[href="/conversation/' . $declined->getId() . '"]'));
        self::assertCount(0, $crawler->filter('.c-home-stays a[href="/conversation/' . $past->getId() . '"]'));

        // The host sees the same stay
        $client->loginUser($host);
        $crawler = $client->request('GET', '/');
        self::assertCount(1, $crawler->filter('.c-home-stays a[href="/conversation/' . $accepted->getId() . '"]'));
    }

    public function testTheCalendarShowsTheStaysOfTheMonth(): void
    {
        $client = static::createClient();
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-2']);
        $host = $members->findOneBy(['username' => 'member-5']);
        $arrival = Carbon::today()->addMonths(2)->startOfMonth()->addDays(10);
        $stay = $this->request($guest, $host, HostingRequest::REQUEST_ACCEPTED, $arrival);

        $client->loginUser($host);
        $crawler = $client->request('GET', '/stays', ['month' => $arrival->format('Y-m')]);

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-stays__bar[data-role="host"][href="/conversation/' . $stay->getId() . '"]'));
        self::assertCount(1, $crawler->filter('.p-stays__list a[href="/conversation/' . $stay->getId() . '"]'));

        // Another month doesn't have it
        $crawler = $client->request('GET', '/stays', ['month' => $arrival->copy()->addMonths(2)->format('Y-m')]);
        // The top bar menu lists the upcoming stays on every page: only the calendar counts here
        self::assertCount(0, $crawler->filter('.p-stays a[href="/conversation/' . $stay->getId() . '"]'));
    }

    public function testOneStayCanBeAddedToACalendar(): void
    {
        $client = static::createClient();
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-2']);
        $host = $members->findOneBy(['username' => 'member-5']);
        $stranger = $members->findOneBy(['username' => 'member-3']);
        $stay = $this->request($guest, $host, HostingRequest::REQUEST_ACCEPTED, Carbon::today()->addDays(4));
        $requestId = $stay->getRequest()->getId();

        $client->loginUser($guest);
        $crawler = $client->request('GET', '/stays');
        self::assertCount(1, $crawler->filter('.p-stays__add a[href="/stays/' . $requestId . '/calendar.ics"]'));

        $client->request('GET', '/stays/' . $requestId . '/calendar.ics');
        $this->assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/calendar; charset=utf-8');
        $ics = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('UID:stay-' . $requestId . '@bewelcome.org', $ics);
        self::assertStringContainsString('/conversation/' . $stay->getId(), $ics);

        // Not a stay of theirs
        $client->loginUser($stranger);
        $client->request('GET', '/stays/' . $requestId . '/calendar.ics');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testTheTopBarStaysCanBeFetchedAgain(): void
    {
        $client = static::createClient();
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-2']);
        $host = $members->findOneBy(['username' => 'member-5']);
        $accepted = $this->request($guest, $host, HostingRequest::REQUEST_ACCEPTED, Carbon::today()->addDays(4));

        $client->loginUser($host);
        $crawler = $client->request('GET', '/stays/nav');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.js-topbar-stays[data-refresh="/stays/nav"]'));
        self::assertCount(1, $crawler->filter('#stays_menu a[href="/conversation/' . $accepted->getId() . '"]'));
    }

    /**
     * A change of the dates after the host accepted is a new request, open again: the stay leaves the lists until the
     * host accepts the new dates, and then it is there once, on the new dates (rox #380).
     */
    public function testAStayChangedAfterItWasAcceptedIsOnTheNewDatesOnly(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-2']);
        $host = $members->findOneBy(['username' => 'member-5']);
        $arrival = Carbon::today()->addMonths(2)->startOfMonth()->addDays(3);
        $accepted = $this->request($guest, $host, HostingRequest::REQUEST_ACCEPTED, $arrival);
        $changed = $this->reply($accepted, $guest, $host, HostingRequest::REQUEST_OPEN, $arrival->copy()->addDays(10));

        $client->loginUser($host);
        $crawler = $client->request('GET', '/stays', ['month' => $arrival->format('Y-m')]);

        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.p-stays__list a[href="/conversation/' . $accepted->getId() . '"]'));
        self::assertCount(0, $crawler->filter('.p-stays__list a[href="/conversation/' . $changed->getId() . '"]'));

        // the host accepts the new dates
        $entityManager->clear();
        $entityManager->find(HostingRequest::class, $changed->getRequest()->getId())->setStatus(HostingRequest::REQUEST_ACCEPTED);
        $entityManager->flush();

        $crawler = $client->request('GET', '/stays', ['month' => $arrival->format('Y-m')]);

        self::assertCount(0, $crawler->filter('.p-stays__list a[href="/conversation/' . $accepted->getId() . '"]'));
        $row = $crawler->filter('.p-stays__list .js-stays-item')->reduce(
            static fn ($item): bool => $item->filter('a[href="/conversation/' . $changed->getId() . '"]')->count() > 0,
        );
        self::assertCount(1, $row);
    }

    /**
     * A reply in the conversation of $message that changes the request: a new request with $status from $arrival.
     */
    private function reply(Message $message, Member $sender, Member $receiver, int $status, Carbon $arrival): Message
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $request = new HostingRequest()
            ->setArrival($arrival)
            ->setDeparture($arrival->copy()->addDays(2))
            ->setFlexible(false)
            ->setNumberOfTravellers(2)
            ->setStatus($status);
        $reply = new Message()
            ->setSubject($message->getSubject())
            ->setParent($message)
            ->setRequest($request)
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setInitiator($message->getInitiator())
            ->setMessage('zztest other dates')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::REQUESTS);
        $entityManager->persist($request);
        $entityManager->persist($reply);
        $entityManager->flush();

        return $reply;
    }

    private function request(Member $guest, Member $host, int $status, Carbon $arrival): Message
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $request = new HostingRequest()
            ->setArrival($arrival)
            ->setDeparture($arrival->copy()->addDays(2))
            ->setFlexible(false)
            ->setNumberOfTravellers(1)
            ->setStatus($status);
        $subject = new Subject()->setSubject('zztest stay');
        $message = new Message()
            ->setSubject($subject)
            ->setRequest($request)
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setMessage('zztest stay')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::REQUESTS);
        $entityManager->persist($request);
        $entityManager->persist($subject);
        $entityManager->persist($message);
        $entityManager->flush();

        return $message;
    }
}
