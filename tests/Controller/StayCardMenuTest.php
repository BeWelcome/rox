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
 * The "⋯" menu of the stay card holds Change dates, the reply templates and Cancel request for the
 * guest; the answer line under the thread has no menu of its own any more.
 */
#[Group('integration')]
class StayCardMenuTest extends WebTestCase
{
    public function testTheGuestFindsChangeDatesTemplatesAndCancelInTheStayCard(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-4']);
        $host = $members->findOneBy(['username' => 'member-6']);

        $request = new HostingRequest()
            ->setArrival(new DateTime('+20 days'))
            ->setDeparture(new DateTime('+23 days'));
        $message = new Message()
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setMessage('zztest request for the stay card menu')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL)
            ->setDeleteRequest('')
            ->setRequest($request);
        $entityManager->persist($request);
        $entityManager->persist($message);
        $entityManager->flush();

        $client->loginUser($guest);
        $crawler = $client->request('GET', '/conversation/' . $message->getId());

        $this->assertResponseIsSuccessful();
        $menu = $crawler->filter('.c-stay-card .c-stay-card__menu');
        self::assertCount(1, $menu);
        self::assertCount(1, $menu->filter('.js-respond-stay-toggle'));
        self::assertCount(3, $menu->filter('.js-conversation-template'));
        self::assertCount(1, $menu->filter('button[type="submit"][form="js-respond-form"][data-confirm]'));
        self::assertCount(0, $crawler->filter('#js-respond-form .js-conversation-template'));
    }
}
