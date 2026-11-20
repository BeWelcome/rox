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
        self::assertCount(1, $crawler->filter('.c-home-stays a[href="/conversation/' . $accepted->getId() . '"] .c-home-stays__role--guest'));

        // The host sees the same stay, as host
        $client->loginUser($host);
        $crawler = $client->request('GET', '/');
        self::assertCount(1, $crawler->filter('.c-home-stays a[href="/conversation/' . $accepted->getId() . '"] .c-home-stays__role--host'));
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
        self::assertCount(0, $crawler->filter('a[href="/conversation/' . $stay->getId() . '"]'));
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
