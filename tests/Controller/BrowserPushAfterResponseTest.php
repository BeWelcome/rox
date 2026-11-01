<?php

namespace App\Tests\Controller;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use App\Entity\Preference;
use App\Service\PushGatewayInterface;
use App\Service\PushSendReport;
use App\Tests\Service\RecordingPushGateway;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A member asks another one to be friends: the other one, who wants browser notifications always, gets the push
 * right after the request (kernel.terminate), not with the next run of send:notifications. A push that fails stays
 * queued for it. Messages, stay requests and invitations are queued the same way.
 */
#[Group('integration')]
class BrowserPushAfterResponseTest extends WebTestCase
{
    private KernelBrowser $client;
    private Connection $connection;
    private RecordingPushGateway $gateway;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->connection = static::getContainer()->get(Connection::class);
        $this->gateway = new RecordingPushGateway();
        static::getContainer()->set(PushGatewayInterface::class, $this->gateway);
    }

    public function testTheReceiverGetsThePushRightAway(): void
    {
        $this->wantsPushes('member-8', 'Always');

        $this->askToBeFriends('member-6', 'member-8');

        self::assertCount(1, $this->gateway->sent);
        self::assertSame('https://fcm.googleapis.com/fcm/send/zztest-member-8', $this->gateway->sent[0]['endpoint']);
        self::assertSame('friendship', $this->gateway->sent[0]['message']['type']);
        self::assertSame([BrowserPushNotification::STATUS_SENT], $this->statuses('member-8'));
    }

    public function testAFailedPushStaysQueuedForSendNotifications(): void
    {
        $this->wantsPushes('member-8', 'Always');
        $this->gateway->report = PushSendReport::failed('push service unavailable');

        $this->askToBeFriends('member-6', 'member-8');

        self::assertCount(1, $this->gateway->sent);
        self::assertSame([BrowserPushNotification::STATUS_SCHEDULED], $this->statuses('member-8'));
    }

    public function testMembersWhoSeeNotificationsOnlyOnTheOpenSiteGetNoPush(): void
    {
        $this->wantsPushes('member-8', 'OpenOnly');

        $this->askToBeFriends('member-6', 'member-8');

        self::assertCount(0, $this->gateway->sent);
        self::assertSame([BrowserPushNotification::STATUS_OPEN_ONLY], $this->statuses('member-8'));
    }

    private function wantsPushes(string $username, string $value): void
    {
        $member = $this->member($username);
        $preferenceId = $this->connection->fetchOne('SELECT id FROM preferences WHERE codeName = ?', [Preference::BROWSER_NOTIFICATIONS]);
        $this->connection->executeStatement('DELETE FROM memberspreferences WHERE IdMember = ? AND IdPreference = ?', [$member->getId(), $preferenceId]);
        $this->connection->executeStatement(
            'INSERT INTO memberspreferences (IdMember, IdPreference, Value, created, updated) VALUES (?, ?, ?, NOW(), NOW())',
            [$member->getId(), $preferenceId, $value]
        );

        $endpoint = 'https://fcm.googleapis.com/fcm/send/zztest-' . $username;
        $subscription = new BrowserPushSubscription()
            ->setMember($member)
            ->setEndpoint($endpoint)
            ->setEndpointHash(BrowserPushSubscription::hashEndpoint($endpoint))
            ->setPublicKey('public-key')
            ->setAuthToken('auth-token')
        ;
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($subscription);
        $entityManager->flush();
    }

    private function askToBeFriends(string $requester, string $receiver): void
    {
        $this->client->loginUser($this->member($requester));
        $crawler = $this->client->request('GET', '/members/' . $receiver . '/friendship/add');
        $this->assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('button[name="add_friendship"]')->form());
        $this->assertResponseRedirects();
    }

    /**
     * @return list<string>
     */
    private function statuses(string $username): array
    {
        return $this->connection->fetchFirstColumn(
            "SELECT status FROM browser_push_notification WHERE member_id = ? AND type = 'friendship' ORDER BY id",
            [$this->member($username)->getId()]
        );
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
