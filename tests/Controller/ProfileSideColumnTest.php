<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The right column of a profile: the Admin box comes first, above the volunteer's own note about the member and
 * everything else (in one column too: order -1).
 */
#[Group('integration')]
class ProfileSideColumnTest extends WebTestCase
{
    public function testTheAdminBoxComesFirst(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $connection->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT m.id, r.id, 10, '\"All\"', 'test', NOW(), NOW() FROM member m, rights r WHERE m.Username = 'member-2' AND r.Name = 'SafetyTeam'
             ON DUPLICATE KEY UPDATE Level = 10",
        );
        $connection->executeStatement(
            "INSERT INTO mycontacts (IdMember, IdContact, Category, Comment, created, updated)
             SELECT a.id, b.id, 'zztest', 'Zztest note', NOW(), NOW() FROM member a, member b WHERE a.Username = 'member-2' AND b.Username = 'member-3'",
        );
        $client->loginUser($entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']));

        $crawler = $client->request('GET', '/members/member-3');

        self::assertResponseIsSuccessful();
        $side = $crawler->filter('.p-pro-layout__side')->first()->children();
        self::assertGreaterThan(1, $side->count());
        self::assertStringContainsString('p-pro-admin-tools', (string) $side->eq(0)->attr('class'));
        self::assertStringContainsString('p-pro-note', (string) $side->eq(1)->attr('class'));
    }
}
