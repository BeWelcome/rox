<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Entity\GroupTranslation;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A member creates a group, as on /new/group: the description is the group's own text (GroupTranslation), so the
 * volunteers who approve the group and later translators see it, not only its creator.
 */
#[TestGroup('integration')]
class GroupCreateTest extends WebTestCase
{
    private const string NAME = 'Zz test cyclists';
    private const string DESCRIPTION = '<p>For members who travel by bike.</p>';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAMemberCreatesAGroup(): void
    {
        $creator = $this->member('member-3');
        $this->client->loginUser($creator);
        $crawler = $this->client->request('GET', '/new/group');
        $this->assertResponseIsSuccessful();

        $this->client->submit($crawler->filter('form[name=group]')->form([
            'group[name]' => self::NAME,
            'group[description]' => self::DESCRIPTION,
            'group[type]' => 'Public',
            'group[membersOnly]' => 'Yes',
        ]));

        $this->assertResponseRedirects();
        $group = $this->group();
        self::assertSame('Yes', $group->getVisiblePosts());
        $descriptions = $group->getDescriptions();
        self::assertSame(['en'], array_keys($descriptions));
        self::assertInstanceOf(GroupTranslation::class, $descriptions['en']);
        self::assertSame(self::DESCRIPTION, $descriptions['en']->getContent());
        self::assertSame($creator->getId(), $descriptions['en']->getTranslator()?->getId());
    }

    public function testTheApprovalPageShowsTheDescription(): void
    {
        $this->client->loginUser($this->member('member-3'));
        $crawler = $this->client->request('GET', '/new/group');
        $this->client->submit($crawler->filter('form[name=group]')->form([
            'group[name]' => self::NAME,
            'group[description]' => self::DESCRIPTION,
            'group[type]' => 'Public',
            'group[membersOnly]' => 'No',
        ]));

        // bwadmin has the Group right (level 10) in the fixtures
        $this->client->loginUser($this->member('bwadmin'));
        $this->client->request('GET', '/admin/groups/approval');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', self::NAME);
        $this->assertSelectorTextContains('table', 'For members who travel by bike.');
    }

    private function group(): Group
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $group = $entityManager->getRepository(Group::class)->findOneBy(['name' => self::NAME]);
        self::assertInstanceOf(Group::class, $group);

        return $group;
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
