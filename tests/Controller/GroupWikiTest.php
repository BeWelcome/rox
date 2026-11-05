<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Entity\Member;
use App\Model\WikiModel;
use App\Wiki\WikiPageName;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A group's Wiki tab: the invitation to start a page, who may write it (the group's members), the page with its
 * links, history and changes. The agora has no wiki.
 */
#[TestGroup('integration')]
final class GroupWikiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAMemberStartsTheGroupsPage(): void
    {
        $group = $this->group('Hiking Enthusiasts');
        $member = $this->member('member-2');
        $this->join($member, $group);
        $this->client->loginUser($member);

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/wiki');

        self::assertResponseIsSuccessful();
        self::assertSame($this->trans('wiki.group.empty.title'), $crawler->filter('.c-wiki-empty__title')->text());
        self::assertSame('/group/' . $group->getId() . '/wiki/create', $crawler->filter('.c-wiki-empty a')->attr('href'));
        self::assertCount(1, $crawler->filter('.p-group-head'), 'the group\'s head');

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/wiki/create');
        self::assertCount(0, $crawler->filter('.c-wiki-categories'));
        $this->client->submit($crawler->filter('form[data-wiki-editor]')->form(), [
            'wiki_page[content]' => "== Meeting up ==\nSee [[Zztest Missing]], [[Group Language Exchange|the exchange]] and [https://example.org/x Example].",
        ]);

        self::assertResponseRedirects('/group/' . $group->getId() . '/wiki');
        $page = $this->wikiModel()->getPage(WikiPageName::forGroup('Hiking Enthusiasts'));
        self::assertSame(['Group_HikingEnthusiasts', 1, 'member-2'], [$page->getPagename(), $page->getVersion(), $page->getAuthor()]);

        $crawler = $this->client->followRedirect();
        // the group's head is the page's head: the tabs are in the sidebar, who edited it closes the card
        self::assertCount(0, $crawler->filter('.c-wiki-head'));
        self::assertCount(1, $crawler->filter('.c-wiki-layout__side > .c-wiki-tabs--block'));
        self::assertStringContainsString('/members/avatar/member-2', (string) $crawler->filter('.c-wiki-article__meta img')->attr('src'));
        self::assertSame(['#wiki-meeting-up'], $crawler->filter('.c-wiki-toc a')->each(static fn (Crawler $a) => $a->attr('href')));
        self::assertSame(
            [
                ['Zztest Missing', $this->trans('wiki.links.main.missing')],
                ['the exchange', $this->trans('wiki.links.group.missing')],
                ['Example', $this->trans('wiki.links.external', ['domain' => 'example.org'])],
            ],
            $crawler->filter('.c-wiki-link-row')->each(static fn (Crawler $row) => [$row->filter('.c-wiki-link-row__title')->text(), $row->filter('.c-wiki-link-row__line')->text()]),
        );
        self::assertSame('/group/' . $this->group('Language Exchange')->getId() . '/wiki', $crawler->filter('.c-wiki-content a.is-group')->attr('href'));
    }

    public function testOnlyTheGroupsMembersWriteIt(): void
    {
        $group = $this->group('Hiking Enthusiasts');
        $this->wikiModel()->save(WikiPageName::forGroup('Hiking Enthusiasts'), 'Hello', 'member-3', 0);
        // not in the group, no volunteer rights
        $this->client->loginUser($this->member('member-6'));

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/wiki');

        self::assertResponseIsSuccessful();
        self::assertSame('Hello', $crawler->filter('.c-wiki-content')->text());
        self::assertSame(
            [$this->trans('wiki.tab.read'), $this->trans('wiki.tab.history') . '1'],
            $crawler->filter('.c-wiki-tabs__item')->each(static fn (Crawler $tab) => $tab->text()),
        );
        foreach (['edit', 'create'] as $action) {
            $this->client->request('GET', '/group/' . $group->getId() . '/wiki/' . $action);
            self::assertResponseStatusCodeSame(403, $action);
        }
    }

    public function testHistoryAndChanges(): void
    {
        $group = $this->group('Hiking Enthusiasts');
        $pageName = WikiPageName::forGroup('Hiking Enthusiasts');
        $this->wikiModel()->save($pageName, 'one', 'member-3', 0);
        $this->wikiModel()->save($pageName, "one\ntwo", 'member-3', 1);
        $this->client->loginUser($this->member('member-2'));
        $base = '/group/' . $group->getId() . '/wiki';

        $crawler = $this->client->request('GET', $base . '/history');
        self::assertResponseIsSuccessful();
        self::assertSame($base . '/history', $crawler->filter('.c-wiki-layout__side .c-wiki-tabs__item.is-active')->attr('href'));
        self::assertSame([$base . '/2', $base . '/1'], $crawler->filter('.c-wiki-btn--outline')->each(static fn (Crawler $a) => $a->attr('href')));
        self::assertSame($base . '/changes/2', $crawler->filter('.c-wiki-btn--soft')->attr('href'));

        $crawler = $this->client->request('GET', $base . '/changes/2');
        self::assertResponseIsSuccessful();
        self::assertSame(['+1', '−0'], $crawler->filter('.c-wiki-diff__count')->each(static fn (Crawler $count) => $count->text()));

        $crawler = $this->client->request('GET', $base . '/1');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.c-wiki-notice a[href="' . $base . '"]'));

        // the main wiki's address of the page leads here, also for a version
        $this->client->request('GET', '/wiki/Group_HikingEnthusiasts/1');
        self::assertResponseRedirects($base . '/1');
    }

    public function testTheAgoraHasNoWiki(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/group/' . Group::BEWELCOME . '/wiki');

        self::assertResponseStatusCodeSame(404);
    }

    private function join(Member $member, Group $group): void
    {
        static::getContainer()->get(EntityManagerInterface::class)->getConnection()->executeStatement(
            'INSERT IGNORE INTO group_membership (member_id, group_id, status, accept_mass_mail, notifications_enabled, created, updated)'
            . " VALUES (?, ?, 'In', 0, 1, NOW(), NOW())",
            [$member->getId(), $group->getId()],
        );
    }

    private function group(string $name): Group
    {
        return static::getContainer()->get(EntityManagerInterface::class)->getRepository(Group::class)->findOneBy(['name' => $name]);
    }

    private function member(string $username): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    private function wikiModel(): WikiModel
    {
        return static::getContainer()->get(WikiModel::class);
    }

    /**
     * @param array<string, int|string> $parameters
     */
    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }
}
