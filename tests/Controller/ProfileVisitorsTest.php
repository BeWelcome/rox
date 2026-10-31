<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Looking at a member's profile puts you in their visitors.
 */
#[Group('integration')]
final class ProfileVisitorsTest extends WebTestCase
{
    public function testAVisitorShowsUpOnTheVisitorsPage(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $this->getMember('member-2');
        $visitor = $this->getMember('member-4');
        $entityManager->getConnection()->executeStatement(
            'DELETE FROM profilesvisits WHERE IdMember = ? AND IdVisitor = ?',
            [$member->getId(), $visitor->getId()],
        );

        $client->loginUser($visitor);
        $client->request('GET', '/members/member-2');
        self::assertResponseIsSuccessful();

        $entityManager->clear();
        $client->loginUser($this->getMember('member-2'));
        $crawler = $client->request('GET', '/members/member-2/visitors');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('member-4', $crawler->text());
    }

    private function getMember(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
