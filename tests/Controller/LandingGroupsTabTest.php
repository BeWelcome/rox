<?php

namespace App\Tests\Controller;

use App\Entity\Group as GroupEntity;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The agora is the BeWelcome group's forum, so the home page has no forum tab: the agora's threads show in the
 * groups tab of the members of the BeWelcome group.
 */
#[Group('integration')]
final class LandingGroupsTabTest extends WebTestCase
{
    private const string AGORA_THREAD = 'Tips for first-time hosts?';
    private const string HIKING_THREAD = 'Weekend hiking meetup - who is in?';

    public function testTheHomePageHasNoForumTab(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getMember('member-2'));

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[aria-controls="groups"]'));
        self::assertCount(0, $crawler->filter('a[aria-controls="threads"]'));
        self::assertCount(0, $crawler->filter('#threadsdisplay'));
    }

    public function testTheGroupsTabShowsTheAgoraToMembersOfTheBeWelcomeGroup(): void
    {
        $client = static::createClient();
        $member = $this->getMember('member-2');
        $this->joinBeWelcomeGroup($member);
        $client->loginUser($member);

        $content = $this->groupsTab($client);

        self::assertStringContainsString(self::AGORA_THREAD, $content);
    }

    public function testTheGroupsTabLeavesOutTheAgoraForOtherMembers(): void
    {
        $client = static::createClient();
        $member = $this->getMember('member-2');
        $this->joinHikingGroup($member);
        $client->loginUser($member);

        $content = $this->groupsTab($client);

        self::assertStringContainsString(self::HIKING_THREAD, $content);
        self::assertStringNotContainsString(self::AGORA_THREAD, $content);
    }

    private function groupsTab(KernelBrowser $client): string
    {
        $client->request('GET', '/widget/threads');
        self::assertResponseIsSuccessful();

        return html_entity_decode((string) $client->getResponse()->getContent(), \ENT_QUOTES);
    }

    private function getMember(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function joinBeWelcomeGroup(Member $member): void
    {
        $this->join($member, GroupEntity::BEWELCOME);
    }

    private function joinHikingGroup(Member $member): void
    {
        $connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $hiking = (int) $connection->fetchOne("SELECT id FROM `group` WHERE name = 'Hiking Enthusiasts'");
        $this->join($member, $hiking);
    }

    private function join(Member $member, int $groupId): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->getConnection()->executeStatement(
            'INSERT IGNORE INTO group_membership (member_id, group_id, status, accept_mass_mail, notifications_enabled, created, updated)'
            . " VALUES (?, ?, 'In', 0, 1, NOW(), NOW())",
            [$member->getId(), $groupId],
        );
        $entityManager->clear();
    }
}
