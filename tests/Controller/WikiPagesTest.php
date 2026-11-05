<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Model\WikiModel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The main wiki: a page with its contents, categories and related pages, saving versions (and two saving at once),
 * creating a page, history, changes, recent changes, the start page, categories and the search.
 */
#[Group('integration')]
final class WikiPagesTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->loginUser($this->member('member-2'));
    }

    public function testAPageShowsItsContentsCategoriesAndRelatedPages(): void
    {
        $this->page('Zztest Safety', "Stay safe.\n[[Category:Zztest hosting]]");
        $this->page('Zztest Guide', 'See [[Zztest Hosting]].');
        $this->page('Zztest Hosting', "Intro\n== Before you say yes ==\nRead [[Zztest Safety|safety tips]] and [[Zztest Missing]].\n=== Keys ===\nText\n[[Category:Zztest hosting]]");

        $crawler = $this->client->request('GET', '/wiki/Zztest_Hosting');

        self::assertResponseIsSuccessful();
        self::assertSame('Zztest Hosting', $crawler->filter('h1')->text());
        self::assertSame(['Zztest hosting', 'Zztest Hosting'], \array_slice($crawler->filter('.c-wiki-crumbs li')->each(static fn (Crawler $li) => $li->text()), 1));
        self::assertSame(
            [$this->trans('wiki.tab.read'), $this->trans('wiki.tab.edit'), $this->trans('wiki.tab.history') . '1'],
            $crawler->filter('.c-wiki-tabs__item')->each(static fn (Crawler $tab) => $tab->text()),
        );
        self::assertSame(['#wiki-before-you-say-yes', '#wiki-keys'], $crawler->filter('.c-wiki-toc a')->each(static fn (Crawler $a) => $a->attr('href')));
        self::assertCount(1, $crawler->filter('h2#wiki-before-you-say-yes'));
        self::assertSame('/wiki/Zztest_Safety', $crawler->filter('.c-wiki-content a:not(.is-missing)')->attr('href'));
        self::assertSame('/wiki/Zztest_Missing/create', $crawler->filter('.c-wiki-content a.is-missing')->attr('href'));
        self::assertSame('/wiki/category/Zztest%20hosting', $crawler->filter('.c-wiki-article__categories a')->attr('href'));
        self::assertSame(
            ['Zztest Safety', 'Zztest Guide'],
            $crawler->filter('.c-wiki-link-row__title')->each(static fn (Crawler $title) => $title->text()),
        );
    }

    public function testSavingAddsAVersionAndSavingAtOnceKeepsTheDraft(): void
    {
        $this->page('Zztest Saving', 'First');

        $crawler = $this->client->request('GET', '/wiki/Zztest_Saving/edit');
        $this->client->submit($crawler->filter('form[data-wiki-editor]')->form(), ['wiki_page[content]' => 'Second']);

        self::assertResponseRedirects('/wiki/Zztest_Saving');
        $page = $this->wikiModel()->getPage('Zztest_Saving');
        self::assertSame([2, 'Second', 'member-2'], [$page->getVersion(), $page->getContent(), $page->getAuthor()]);
        $crawler = $this->client->followRedirect();
        self::assertSame($this->trans('wiki.flash.updated'), $crawler->filter('.c-inline-flash__text')->text());

        // someone else saves while member-2 edits
        $crawler = $this->client->request('GET', '/wiki/Zztest_Saving/edit');
        $form = $crawler->filter('form[data-wiki-editor]')->form();
        $this->wikiModel()->save('Zztest_Saving', 'Third', 'member-3', 2);
        $crawler = $this->client->submit($form, ['wiki_page[content]' => 'My draft']);

        self::assertResponseStatusCodeSame(422);
        self::assertSame($this->trans('wiki.conflict', ['version' => 3]), $crawler->filter('.c-wiki-notice--error')->text());
        self::assertSame('My draft', $crawler->filter('textarea')->text());
        self::assertSame('3', $crawler->filter('input[name="wiki_page[version]"]')->attr('value'));
        self::assertSame('Third', $this->wikiModel()->getPage('Zztest_Saving')->getContent());

        // saving again makes the draft the next version
        $this->client->submit($crawler->filter('form[data-wiki-editor]')->form());
        self::assertResponseRedirects('/wiki/Zztest_Saving');
        self::assertSame([4, 'My draft'], [$this->wikiModel()->getPage('Zztest_Saving')->getVersion(), $this->wikiModel()->getPage('Zztest_Saving')->getContent()]);
    }

    public function testAMissingPageIsCreated(): void
    {
        $this->client->request('GET', '/wiki/Zztest_New');
        self::assertResponseRedirects('/wiki/Zztest_New/create');

        $crawler = $this->client->followRedirect();
        self::assertSame($this->trans('wiki.create.notice'), $crawler->filter('.c-wiki-notice')->text());
        self::assertCount(0, $crawler->filter('.c-wiki-tabs'));
        $crawler = $this->client->submit($crawler->filter('form[data-wiki-editor]')->form(), ['wiki_page[content]' => '  ']);
        self::assertResponseStatusCodeSame(422);
        self::assertCount(1, $crawler->filter('.c-wiki-notice--error'));
        self::assertNull($this->wikiModel()->getPage('Zztest_New'));

        $this->client->submit($crawler->filter('form[data-wiki-editor]')->form(), ['wiki_page[content]' => "== Hello ==\nWorld"]);

        self::assertResponseRedirects('/wiki/Zztest_New');
        self::assertSame(1, $this->wikiModel()->getPage('Zztest_New')->getVersion());
        $this->client->request('GET', '/wiki/Zztest_New/create');
        self::assertResponseRedirects('/wiki/Zztest_New/edit');
    }

    public function testThePageOfAVisualEditorAndOfMarkupOnly(): void
    {
        $this->page('Zztest Simple', "== A ==\n'''b''' [[Zztest Other]]\n; Term\n: Description");
        $this->page('Zztest Table', "{|\n| a\n|}");

        $crawler = $this->client->request('GET', '/wiki/Zztest_Simple/edit');
        self::assertSame('visual', $crawler->filter('form[data-wiki-editor]')->attr('data-wiki-editor'));
        self::assertCount(0, $crawler->filter('.c-wiki-editor__note'));

        $crawler = $this->client->request('GET', '/wiki/Zztest_Table/edit');
        self::assertSame('markup', $crawler->filter('form[data-wiki-editor]')->attr('data-wiki-editor'));
        self::assertSame(
            $this->trans('wiki.editor.markup.only.at', ['what' => $this->trans('wiki.editor.blocker.table'), 'line' => 1]),
            $crawler->filter('.c-wiki-editor__note')->text(),
        );
        self::assertNotNull($crawler->filter('[data-wiki-mode="visual"]')->attr('disabled'));
    }

    public function testHistoryChangesAndAnOlderVersion(): void
    {
        $this->page('Zztest History', "one\ntwo");
        $this->wikiModel()->save('Zztest_History', "one\ntwo\nthree", 'member-3', 1);
        $this->wikiModel()->save('Zztest_History', "one\nthree", 'member-2', 2);

        $crawler = $this->client->request('GET', '/wiki/Zztest_History/history');

        self::assertResponseIsSuccessful();
        self::assertSame(
            [$this->trans('wiki.version', ['version' => 3]) . ' ' . $this->trans('wiki.history.current'), $this->trans('wiki.version', ['version' => 2]), $this->trans('wiki.version', ['version' => 1])],
            $crawler->filter('.c-wiki-version__title')->each(static fn (Crawler $title) => $title->text()),
        );
        self::assertSame(['−4', '+6', $this->trans('wiki.history.new')], $crawler->filter('.c-wiki-delta')->each(static fn (Crawler $delta) => $delta->text()));
        self::assertSame(['/wiki/Zztest_History/changes/3', '/wiki/Zztest_History/changes/2'], $crawler->filter('.c-wiki-btn--soft')->each(static fn (Crawler $a) => $a->attr('href')));

        $crawler = $this->client->request('GET', '/wiki/Zztest_History/changes/3');
        self::assertResponseIsSuccessful();
        self::assertSame(['same', 'removed', 'same'], $crawler->filter('.c-wiki-diff__line')->each(static fn (Crawler $line) => substr((string) $line->attr('class'), \strlen('c-wiki-diff__line is-'))));
        self::assertSame(['+0', '−1'], $crawler->filter('.c-wiki-diff__count')->each(static fn (Crawler $count) => $count->text()));
        self::assertSame('/wiki/Zztest_History/changes/2', $crawler->filter('.c-wiki-diff__nav a')->attr('href'));

        $this->client->request('GET', '/wiki/Zztest_History/changes/1');
        self::assertResponseStatusCodeSame(404);

        $crawler = $this->client->request('GET', '/wiki/Zztest_History/1');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString("one\ntwo", $crawler->filter('.c-wiki-content')->text(null, false));
        self::assertCount(1, $crawler->filter('.c-wiki-notice a[href="/wiki/Zztest_History"]'));
        self::assertSame($this->trans('wiki.version.of', ['version' => 1, 'latest' => 3]), $crawler->filter('.c-wiki-meta span')->last()->text());

        $this->client->request('GET', '/wiki/Zztest_History/9');
        self::assertResponseStatusCodeSame(404);
    }

    public function testRecentChangesTheStartPageAndACategory(): void
    {
        if (null === $this->wikiModel()->getPage('WikiFrontPage')) {
            $this->page('WikiFrontPage', 'Welcome');
        }
        $this->page('Zztest Recent', "Text about hosting.\n[[Category:Zztest browse]]");
        $this->wikiModel()->save('Zztest_Recent', "More text about hosting.\n[[Category:Zztest browse]]", 'member-2', 1);
        // the newest edits of all
        static::getContainer()->get(EntityManagerInterface::class)->getConnection()->executeStatement(
            "UPDATE ewiki SET created = UNIX_TIMESTAMP() + version WHERE pagename = 'Zztest_Recent'",
        );

        $crawler = $this->client->request('GET', '/wiki/recent');
        self::assertResponseIsSuccessful();
        $first = $crawler->filter('.c-wiki-version')->first();
        self::assertSame('Zztest Recent', $first->filter('.c-wiki-version__title')->text());
        self::assertSame('/wiki/Zztest_Recent/changes/2', $first->filter('.c-wiki-btn--soft')->attr('href'));

        $crawler = $this->client->request('GET', '/wiki');
        self::assertResponseIsSuccessful();
        self::assertSame($this->trans('wiki.start.page'), $crawler->filter('h1')->text());
        self::assertSame('Zztest Recent', $crawler->filter('.c-wiki-person-row__title')->first()->text());
        self::assertSame('1', $crawler->filter('a.c-wiki-category-row[href="/wiki/category/Zztest%20browse"] .c-wiki-count')->text());

        $crawler = $this->client->request('GET', '/wiki/category/Zztest%20browse');
        self::assertResponseIsSuccessful();
        self::assertSame('More text about hosting.', $crawler->filter('.c-wiki-version__line')->text());
    }

    public function testTheSearchSuggestsTitlesFirst(): void
    {
        $this->page('Zztest Find Body', 'All about zzfindme here.');
        $this->page('Zztest zzfindme', 'Title match');

        $this->client->request('GET', '/wiki/search/suggest?q=zzfindme');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['Zztest zzfindme', 'Zztest Find Body'], array_column($data['results'], 'title'));
        self::assertSame('All about zzfindme here.', $data['results'][1]['snippet']);
        self::assertSame('/wiki/zzfindme/create', $data['create']);

        $this->client->request('GET', '/wiki/search/suggest?q=Zztest+zzfindme');
        self::assertNull(json_decode((string) $this->client->getResponse()->getContent(), true)['create']);

        $crawler = $this->client->request('GET', '/wiki/search?q=zzfindme');
        self::assertResponseIsSuccessful();
        self::assertCount(2, $crawler->filter('.c-wiki-list .c-wiki-version'));
    }

    public function testAGroupsPageLeadsToTheGroup(): void
    {
        $group = static::getContainer()->get(EntityManagerInterface::class)->getRepository(\App\Entity\Group::class)->findOneBy(['name' => 'Hiking Enthusiasts']);

        $this->client->request('GET', '/wiki/Group_HikingEnthusiasts');

        self::assertResponseRedirects('/group/' . $group->getId() . '/wiki');
    }

    private function page(string $title, string $content): void
    {
        $this->wikiModel()->createWikiPage($title, $content, 'member-3');
    }

    private function wikiModel(): WikiModel
    {
        return static::getContainer()->get(WikiModel::class);
    }

    private function member(string $username): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    /**
     * A text as the page shows it: the test database may have no translations (CI), then the key.
     *
     * @param array<string, int|string> $parameters
     */
    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }
}
