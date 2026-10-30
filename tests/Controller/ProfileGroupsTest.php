<?php

namespace App\Tests\Controller;

use App\Entity\Group as GroupEntity;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * Members choose which of their groups their profile shows.
 */
#[Group('integration')]
final class ProfileGroupsTest extends WebTestCase
{
    private KernelBrowser $client;
    private Member $member;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->member = $this->getMember('member-2');
        foreach (['Hiking Enthusiasts', 'Language Exchange'] as $name) {
            $this->join($this->getGroup($name));
        }
    }

    public function testAMemberPicksTheGroupsTheirProfileShows(): void
    {
        $this->client->loginUser($this->member);
        $hiking = $this->getGroup('Hiking Enthusiasts');

        $crawler = $this->request('/groups/mygroups');
        $form = $crawler->filter('form[name="profile_groups"]')->form();
        foreach ($form['profile_groups[groups]'] as $checkbox) {
            self::assertInstanceOf(ChoiceFormField::class, $checkbox);
            if ((string) $hiking->getId() === $checkbox->availableOptionValues()[0]) {
                $checkbox->untick();
            }
        }
        $this->client->submit($form);
        self::assertResponseRedirects('/groups/mygroups');

        // another member looks at the profile
        $this->client->loginUser($this->getMember('member-4'));
        $profile = $this->request('/members/member-2')->text();
        self::assertStringContainsString('Language Exchange', $profile);
        self::assertStringNotContainsString('Hiking Enthusiasts', $profile);

        $groups = $this->request('/members/member-2/groups')->filter('#member-groups')->text();
        self::assertStringContainsString('Language Exchange', $groups);
        self::assertStringNotContainsString('Hiking Enthusiasts', $groups);
    }

    public function testTheGroupsMemberSettingsHaveTheChoiceToo(): void
    {
        $this->client->loginUser($this->member);
        $hiking = $this->getGroup('Hiking Enthusiasts');

        $crawler = $this->request('/group/' . $hiking->getId() . '/membersettings');
        $form = $crawler->filter('form[name="membership_settings"]')->form();
        $shown = $form['membership_settings[shownOnProfile]'];
        self::assertInstanceOf(ChoiceFormField::class, $shown);
        $shown->untick();
        $this->client->submit($form);

        self::assertSame(0, (int) $this->connection()->fetchOne(
            'SELECT shown_on_profile FROM group_membership WHERE member_id = ? AND group_id = ?',
            [$this->member->getId(), $hiking->getId()],
        ));
    }

    private function request(string $uri): Crawler
    {
        static::getContainer()->get(EntityManagerInterface::class)->clear();
        $crawler = $this->client->request('GET', $uri);
        self::assertResponseIsSuccessful();

        return $crawler;
    }

    private function join(GroupEntity $group): void
    {
        $this->connection()->executeStatement(
            "INSERT INTO group_membership (member_id, group_id, status, accept_mass_mail, notifications_enabled, shown_on_profile, created, updated)
             VALUES (?, ?, 'In', 0, 1, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE status = 'In', shown_on_profile = 1",
            [$this->member->getId(), $group->getId()],
        );
    }

    private function connection(): Connection
    {
        return static::getContainer()->get(EntityManagerInterface::class)->getConnection();
    }

    private function getGroup(string $name): GroupEntity
    {
        $group = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(GroupEntity::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(GroupEntity::class, $group);

        return $group;
    }

    private function getMember(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
