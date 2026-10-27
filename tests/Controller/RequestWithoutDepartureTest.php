<?php

namespace App\Tests\Controller;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Production has requests without a departure (NULL, 555 of them from 2019 to 2023). The inbox and the
 * conversation must show them instead of failing on the unset typed property.
 */
#[Group('integration')]
class RequestWithoutDepartureTest extends WebTestCase
{
    private KernelBrowser $client;
    private Member $guest;
    private Message $message;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $this->guest = $members->findOneBy(['username' => 'member-2']);
        $host = $members->findOneBy(['username' => 'member-3']);

        $request = new HostingRequest()
            ->setArrival(new DateTime('2021-06-01'))
            ->setDeparture(new DateTime('2021-06-03'));
        $this->message = new Message()
            ->setSender($this->guest)
            ->setReceiver($host)
            ->setMessage('An old request without a departure')
            ->setStatus(MessageStatusType::SENT)
            ->setDeleteRequest('')
            ->setRequest($request);
        $entityManager->persist($request);
        $entityManager->persist($this->message);
        $entityManager->flush();

        static::getContainer()->get(Connection::class)
            ->executeStatement('UPDATE request SET departure = NULL WHERE id = ?', [$request->getId()]);
        $entityManager->clear();
    }

    public function testTheRequestsListShowsIt(): void
    {
        $this->client->loginUser($this->reload($this->guest));

        $this->client->request('GET', '/requests/');

        $this->assertResponseIsSuccessful();
    }

    public function testTheConversationShowsIt(): void
    {
        $this->client->loginUser($this->reload($this->guest));

        $this->client->request('GET', '/conversation/' . $this->message->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'An old request without a departure');
    }

    private function reload(Member $member): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)->find(Member::class, $member->getId());
    }
}
