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
 * Actions that send the member back to the page they came from: the Referer header is optional, and only pages of
 * this site count. Without it they go to a fallback page instead of failing with a 500.
 */
#[Group('integration')]
class RedirectBackTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = static::getContainer()->get(Connection::class);
    }

    public function testRequestLimitWithoutRefererGoesToTheHostsProfile(): void
    {
        [$guest, $host] = $this->limitedGuestAndHost(requests: true);
        $this->client->loginUser($guest);

        $this->client->request('GET', '/new/request/' . $host->getUsername());

        $this->assertResponseRedirects('/members/' . $host->getUsername());
    }

    public function testRequestLimitWithRefererGoesBackThere(): void
    {
        [$guest, $host] = $this->limitedGuestAndHost(requests: true);
        $this->client->loginUser($guest);

        $this->client->request('GET', '/new/request/' . $host->getUsername(), server: ['HTTP_REFERER' => 'http://localhost/search/locations']);

        $this->assertResponseRedirects('http://localhost/search/locations');
    }

    public function testARefererOfAnotherSiteIsNotFollowed(): void
    {
        [$guest, $host] = $this->limitedGuestAndHost(requests: true);
        $this->client->loginUser($guest);

        $this->client->request('GET', '/new/request/' . $host->getUsername(), server: ['HTTP_REFERER' => 'https://example.org/']);

        $this->assertResponseRedirects('/members/' . $host->getUsername());
    }

    public function testMessageLimitWithoutRefererGoesToTheReceiversProfile(): void
    {
        [$sender, $receiver] = $this->limitedGuestAndHost(requests: false);
        $this->client->loginUser($sender);

        $this->client->request('GET', '/new/message/' . $receiver->getUsername());

        $this->assertResponseRedirects('/members/' . $receiver->getUsername());
    }

    public function testForumGroupFilterWithoutRefererGoesToTheForums(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/forums/show/groups/only-mine');

        $this->assertResponseRedirects('/forums');
    }

    public function testTranslationModeWithoutRefererGoesToTheTranslations(): void
    {
        $this->client->loginUser($this->member('bwadmin'));

        $this->client->request('GET', '/admin/translations/mode/on');

        $this->assertResponseRedirects('/admin/translations');
    }

    public function testAVolunteerToolWithoutTheRightIsDenied(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/admin/tools/topspammer');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Two active members; the first has no positive comment and sent 6 requests (or messages) to the second during
     * the last hour, so they reached the limit for new members.
     *
     * @return array{Member, Member}
     */
    private function limitedGuestAndHost(bool $requests): array
    {
        $ids = $this->connection->fetchFirstColumn(
            "SELECT m.id FROM member m WHERE m.Status = 'Active' AND m.Username <> 'bwadmin'
             AND NOT EXISTS (SELECT 1 FROM comment c WHERE c.to_member_id = m.id AND c.quality = 'positive')
             ORDER BY m.id LIMIT 2"
        );
        self::assertCount(2, $ids, 'the fixtures need two active members without a positive comment');
        [$guest, $host] = [$this->entityManager->find(Member::class, $ids[0]), $this->entityManager->find(Member::class, $ids[1])];

        for ($i = 0; $i < 6; ++$i) {
            $message = new Message()
                ->setSender($guest)
                ->setReceiver($host)
                ->setMessage('Hello ' . $i)
                ->setStatus(MessageStatusType::SENT)
                ->setDeleteRequest('');
            if ($requests) {
                $hostingRequest = new HostingRequest()
                    ->setArrival(new DateTime('+10 days'))
                    ->setDeparture(new DateTime('+12 days'));
                $this->entityManager->persist($hostingRequest);
                $message->setRequest($hostingRequest);
            }
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();

        return [$guest, $host];
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
