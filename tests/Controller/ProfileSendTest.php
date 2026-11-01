<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * "Send" on a profile writes a message: it doesn't open the hosting request the other member sent.
 */
#[Group('integration')]
class ProfileSendTest extends WebTestCase
{
    public function testSendDoesNotOpenTheOtherMembersRequest(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-4']);
        $host = $members->findOneBy(['username' => 'member-6']);

        $request = new HostingRequest()
            ->setArrival(new DateTime('+10 days'))
            ->setDeparture(new DateTime('+14 days'));
        $message = new Message()
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setMessage('zztest request for Send')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL)
            ->setDeleteRequest('')
            ->setRequest($request);
        $entityManager->persist($request);
        $entityManager->persist($message);
        $entityManager->flush();

        $client->loginUser($host);
        $crawler = $client->request('GET', '/members/member-4');

        $this->assertResponseIsSuccessful();
        $send = $crawler->filter('a.p-pro__btn--message');
        self::assertGreaterThan(0, $send->count());
        foreach ($send->each(static fn ($link): string => (string) $link->attr('href')) as $href) {
            self::assertStringNotContainsString('/conversation/' . $message->getId(), $href);
        }
    }
}
