<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Members who just signed up (MailToConfirm, MailConfirmed, Activated) aren't shown to other members, except to
 * volunteers.
 */
#[Group('integration')]
class NewMemberVisibilityTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private string $newMember;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = static::getContainer()->get(Connection::class);
        $this->newMember = 'member-3';
        $this->connection->executeStatement("UPDATE member SET Status = 'MailToConfirm' WHERE Username = ?", [$this->newMember]);
    }

    public function testOtherMembersDontSeeANewMember(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/members/' . $this->newMember);
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('GET', '/search/members/' . $this->newMember . '/preview');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testANewMemberSeesTheirOwnProfile(): void
    {
        $this->client->loginUser($this->member($this->newMember));

        $this->client->request('GET', '/members/' . $this->newMember);

        $this->assertResponseIsSuccessful();
    }

    public function testACheckerSeesANewMember(): void
    {
        $checker = $this->member('member-2');
        $this->connection->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT ?, r.id, 10, '\"All\"', 'test', NOW(), NOW() FROM rights r WHERE r.Name = 'Checker'",
            [$checker->getId()]
        );
        $this->entityManager->refresh($checker);
        $this->client->loginUser($checker);

        $this->client->request('GET', '/members/' . $this->newMember);

        $this->assertResponseIsSuccessful();
    }

    private function member(string $username): Member
    {
        $this->entityManager->clear();
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
