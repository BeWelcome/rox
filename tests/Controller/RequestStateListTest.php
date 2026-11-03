<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * In the conversation list a request shows where it stands as an icon on the avatar and its dates in a
 * pill of the same colour: new while unread, waiting once read, crossed out once declined.
 */
#[Group('integration')]
class RequestStateListTest extends WebTestCase
{
    public function testTheListShowsWhereTheRequestStands(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $host = $members->findOneBy(['username' => 'member-2']);
        $guest = $members->findOneBy(['username' => 'member-5']);

        $request = new HostingRequest()
            ->setArrival(new DateTime('2030-11-10'))
            ->setDeparture(new DateTime('2030-11-14'));
        $subject = new Subject()->setSubject('zztest state subject');
        $message = new Message()
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setSubject($subject)
            ->setRequest($request)
            ->setMessage('zztest state')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL);
        $entityManager->persist($request);
        $entityManager->persist($subject);
        $entityManager->persist($message);
        $entityManager->flush();

        $client->loginUser($host);
        $item = $client->request('GET', '/conversations/')
            ->filter('.c-inbox-item__link[href*="/conversation/' . $message->getId() . '"]');
        self::assertCount(1, $item->filter('.c-req-kind--new'));
        self::assertStringContainsString('10–14 Nov', $item->filter('.c-req-pill--new')->text());

        // the guest sent it, so for them it waits
        $client->loginUser($guest);
        $item = $client->request('GET', '/conversations/')
            ->filter('.c-inbox-item__link[href*="/conversation/' . $message->getId() . '"]');
        self::assertCount(1, $item->filter('.c-req-kind--pending'));
        self::assertCount(1, $item->filter('.c-req-pill--pending'));

        // The browser rebooted the kernel, which cleared the entity manager: change the row directly
        static::getContainer()->get(Connection::class)
            ->executeStatement('UPDATE request SET status = ? WHERE id = ?', [HostingRequest::REQUEST_DECLINED, $request->getId()]);
        $crawler = $client->request('GET', '/conversations/');
        $item = $crawler->filter('.c-inbox-item__link[href*="/conversation/' . $message->getId() . '"]');
        self::assertCount(1, $item->filter('.c-req-pill--declined'));
        self::assertCount(1, $crawler->filter('.c-inbox-item.is-closed .c-inbox-item__link[href*="/conversation/' . $message->getId() . '"]'));
    }

    public function testARequestWithoutAnswerBeforeItsArrivalIsExpired(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $host = $members->findOneBy(['username' => 'member-2']);
        $guest = $members->findOneBy(['username' => 'member-5']);

        $request = new HostingRequest()
            ->setArrival(new DateTime('-3 days'))
            ->setDeparture(new DateTime('-1 day'));
        $subject = new Subject()->setSubject('zztest expired subject');
        $message = new Message()
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setSubject($subject)
            ->setRequest($request)
            ->setMessage('zztest expired')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL);
        $entityManager->persist($request);
        $entityManager->persist($subject);
        $entityManager->persist($message);
        $entityManager->flush();

        $client->loginUser($host);
        $item = $client->request('GET', '/conversations/')
            ->filter('.c-inbox-item__link[href*="/conversation/' . $message->getId() . '"]');
        self::assertCount(1, $item->filter('.c-req-kind--expired'));
        self::assertCount(1, $item->filter('.c-req-pill--expired'));
        self::assertStringContainsString('request.status.expired', $item->filter('.c-req-status--expired')->text());

        $crawler = $client->request('GET', '/conversation/' . $message->getId());
        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.c-stay2--expired'));
        self::assertStringContainsString('request.status.expired', $crawler->filter('.c-stay2__status')->text());
    }
}
