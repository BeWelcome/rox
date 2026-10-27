<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
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
 * The reports of the volunteer tools and the log list, read from member, address, location and messages (not
 * main's members, geonames and geonamescountries tables).
 */
#[Group('integration')]
class VolunteerToolReportsTest extends WebTestCase
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

    public function testTopSpammerListsBannedMembersWithTheirMessages(): void
    {
        [$spammer, $receiver] = $this->members(2);
        $this->send($spammer, $receiver, 3);
        $this->setStatus($spammer, MemberStatusType::BANNED);

        $this->open('/admin/tools/topspammer');

        $this->assertSelectorTextContains('main', $spammer->getUsername());
    }

    public function testDamageDoneListsMembersWhoLeftAfterASpammerWrote(): void
    {
        [$spammer, $receiver] = $this->members(2);
        $this->send($spammer, $receiver, 1);
        $this->setStatus($spammer, MemberStatusType::REJECTED);
        $this->setStatus($receiver, MemberStatusType::TAKEN_OUT);

        $this->open('/admin/tools/damagedone');

        $this->assertSelectorTextContains('main', $spammer->getUsername());
        $this->assertSelectorTextContains('main', $receiver->getUsername());
    }

    public function testMessagesAndRequestsSentListFrequentSendersWithTheirPlace(): void
    {
        [$messenger, $requester, $receiver] = $this->members(3);
        $this->send($messenger, $receiver, 10);
        $this->send($requester, $receiver, 10, request: true);
        $place = $this->connection->fetchOne(
            'SELECT l.name FROM address a JOIN location l ON l.geoname_id = a.location WHERE a.member_id = ? AND a.active = 1',
            [$requester->getId()]
        );

        $this->open('/admin/tools/messages/sent');
        $this->assertSelectorTextContains('main', $messenger->getUsername());

        $this->open('/admin/tools/requests/sent');
        $this->assertSelectorTextContains('main', $requester->getUsername());
        $this->assertStringNotContainsString($messenger->getUsername(), $this->client->getCrawler()->filter('main')->text());
        if (false !== $place) {
            $this->assertSelectorTextContains('main', $place);
        }
    }

    public function testAgeByCountryListsTheCountriesOfActiveMembers(): void
    {
        $country = $this->connection->fetchOne(
            "SELECT c.name FROM member m
             JOIN address a ON a.member_id = m.id AND a.active = 1
             JOIN location l ON l.geoname_id = a.location
             JOIN location c ON c.geoname_id = l.country
             WHERE m.Status = 'Active' AND m.BirthDate IS NOT NULL
             GROUP BY c.geoname_id ORDER BY COUNT(*) DESC LIMIT 1"
        );
        self::assertNotFalse($country, 'the fixtures need active members with an address');

        $this->open('/admin/tools/countryage');

        $this->assertSelectorTextContains('main', $country);
    }

    public function testTheLogListShowsTheLogsWithUsernames(): void
    {
        [$member] = $this->members(1);
        $this->connection->executeStatement(
            "INSERT INTO logs (Str, Type, created, idMember) VALUES ('volunteer tool report test', 'Login', NOW(), ?)",
            [$member->getId()]
        );

        $this->open('/admin/logs?log[username]=' . $member->getUsername());

        $this->assertSelectorTextContains('body', 'volunteer tool report test');
        $this->assertSelectorTextContains('body', $member->getUsername());
    }

    private function open(string $url): void
    {
        $admin = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        foreach (['Checker', 'SafetyTeam', 'Logs'] as $right) {
            $this->connection->executeStatement(
                "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
                 SELECT ?, r.id, 10, '\"All\"', 'test', NOW(), NOW() FROM rights r
                 WHERE r.Name = ? AND NOT EXISTS (
                     SELECT 1 FROM rightsvolunteers rv WHERE rv.IdMember = ? AND rv.IdRight = r.id
                 )",
                [$admin->getId(), $right, $admin->getId()]
            );
        }
        $this->entityManager->refresh($admin);
        $this->client->loginUser($admin);

        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return list<Member> active members other than bwadmin
     */
    private function members(int $count): array
    {
        $ids = $this->connection->fetchFirstColumn(
            "SELECT id FROM member WHERE Status = 'Active' AND Username <> 'bwadmin' ORDER BY id LIMIT " . $count
        );

        return array_map(fn (int|string $id): Member => $this->entityManager->find(Member::class, $id), $ids);
    }

    private function send(Member $sender, Member $receiver, int $count, bool $request = false): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $message = new Message()
                ->setSender($sender)
                ->setReceiver($receiver)
                ->setMessage('Hello ' . $i)
                ->setStatus(MessageStatusType::SENT)
                ->setDeleteRequest('');
            if ($request) {
                $hostingRequest = new HostingRequest()
                    ->setArrival(new DateTime('+10 days'))
                    ->setDeparture(new DateTime('+12 days'));
                $this->entityManager->persist($hostingRequest);
                $message->setRequest($hostingRequest);
            }
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    private function setStatus(Member $member, string $status): void
    {
        $this->connection->executeStatement(
            'UPDATE member SET Status = ?, updated = NOW() WHERE id = ?',
            [$status, $member->getId()]
        );
    }
}
