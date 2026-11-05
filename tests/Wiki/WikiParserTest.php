<?php

namespace App\Tests\Wiki;

use App\Wiki\ParsedWikiPage;
use App\Wiki\WikiLink;
use App\Wiki\WikiLinkResolverInterface;
use App\Wiki\WikiPageName;
use App\Wiki\WikiParser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The wiki markup: the editor's markup, what older pages use, links of every kind, categories and the table of
 * contents. Pages "Hosting tips" and the group wiki of Lisbon exist; others don't.
 */
final class WikiParserTest extends KernelTestCase
{
    public function testTheToolbarsMarkup(): void
    {
        $page = $this->parse("Intro with '''bold''' and ''italic''.\nSame paragraph.\n\n== Before you say yes ==\n* one\n* two\n\n=== Keys ===\n# first\n# second\n\n----\n==== Small ====");

        self::assertSame(
            "<p>Intro with <strong>bold</strong> and <em>italic</em>.\nSame paragraph.</p>\n"
            . "<h2 id=\"wiki-before-you-say-yes\">Before you say yes</h2>\n"
            . "<ul><li>one</li><li>two</li></ul>\n"
            . "<h3 id=\"wiki-keys\">Keys</h3>\n"
            . "<ol><li>first</li><li>second</li></ol>\n"
            . "<hr>\n"
            . "<h4 id=\"wiki-small\">Small</h4>\n",
            $page->html,
        );
        self::assertSame([
            ['level' => 2, 'text' => 'Before you say yes', 'id' => 'wiki-before-you-say-yes'],
            ['level' => 3, 'text' => 'Keys', 'id' => 'wiki-keys'],
        ], $page->headings);
        self::assertTrue($page->visualEditable);
    }

    public function testLinksOfEveryKind(): void
    {
        $page = $this->parse('[[Hosting tips]], [[Hosting with kids|kids]], [[Group Lisbon|Lisbon group]], [https://example.org/a?b=1&c=2 Example], https://bewelcome.org/about. and [[:Category:Safety]]');

        self::assertStringContainsString('<a href="/wiki/Hosting_tips" class="c-wiki-link" title="Hosting tips">Hosting tips</a>', $page->html);
        self::assertStringContainsString('<a href="/wiki/Hosting_with_kids/create" class="c-wiki-link is-missing" title="wiki.link.missing">kids</a>', $page->html);
        self::assertStringContainsString('<a href="/group/7/wiki" class="c-wiki-link is-group" title="Group Lisbon"><i class="fas fa-users c-wiki-link__icon"></i>Lisbon group</a>', $page->html);
        self::assertStringContainsString('<a href="https://example.org/a?b=1&amp;c=2" class="c-wiki-link is-external">Example<i', $page->html);
        // the full stop after a bare address isn't part of it
        self::assertStringContainsString('<a href="https://bewelcome.org/about" class="c-wiki-link is-external">https://bewelcome.org/about<i class="fas fa-arrow-up-right-from-square c-wiki-link__icon"></i></a>. and', $page->html);
        self::assertStringContainsString('<a href="/wiki/category/Safety"', $page->html);

        self::assertSame(
            [
                [WikiLink::PAGE, 'Hosting_tips', true],
                [WikiLink::PAGE, 'Hosting_with_kids', false],
                [WikiLink::GROUP, 'Group_Lisbon', true],
                [WikiLink::EXTERNAL, 'https://example.org/a?b=1&c=2', true],
                [WikiLink::CATEGORY, 'Safety', true],
                // bare addresses come last
                [WikiLink::EXTERNAL, 'https://bewelcome.org/about', true],
            ],
            array_map(static fn (WikiLink $link) => [$link->kind, $link->target, $link->exists], $page->links),
        );
        self::assertSame('kids', $page->links[1]->label);
    }

    public function testCategoriesArePulledOut(): void
    {
        $page = $this->parse("Text\n[[Category:Hosting]]\n[[Category:Safety_tips]]\nMore [[category:Hosting]]");

        self::assertSame(['Hosting', 'Safety tips'], $page->categories);
        self::assertSame("<p>Text\nMore</p>\n", $page->html);
        self::assertSame(['Hosting', 'Safety tips'], WikiParser::categoriesOf("[[Category:Hosting]]\n[[Category:Safety_tips]]"));
    }

    public function testOlderMarkup(): void
    {
        $page = $this->parse("= Title =\n; Term : Definition\n: indented\n* a\n** nested\n* b\n\n preformatted '''bold'''\n\n{| class=\"wikitable\" onclick=\"x\"\n! A !! B\n|-\n| 1 || 2\n|}\n{{user|anna_k}} {{Deprecated}} [[Image:Map.png|thumb|The map]] __TOC__ <span style=\"color:red\">red</span> <script>x</script> <nowiki>'''raw'''</nowiki>");

        self::assertStringContainsString('<h2 id="wiki-title">Title</h2>', $page->html);
        self::assertStringContainsString("<dl><dt>Term</dt><dd>Definition</dd><dd>indented</dd></dl>\n", $page->html);
        self::assertStringContainsString("<ul><li>a<ul><li>nested</li></ul>\n</li><li>b</li></ul>\n", $page->html);
        self::assertStringContainsString("<pre>preformatted <strong>bold</strong></pre>\n", $page->html);
        self::assertStringContainsString('<table class="wikitable" onclick="x"><tr><th>A</th><th>B</th></tr><tr><td>1</td><td>2</td></tr></table>', $page->html);
        self::assertStringContainsString('<a href="/members/anna_k" class="c-wiki-link">anna_k</a>', $page->html);
        self::assertStringContainsString('<span class="c-wiki-template">Deprecated</span>', $page->html);
        self::assertStringContainsString('The map', $page->html);
        self::assertStringNotContainsString('TOC', $page->html);
        // HTML passes for the purifier; scripts are dropped
        self::assertStringContainsString('<span style="color:red">red</span>', $page->html);
        self::assertStringNotContainsString('script', $page->html);
        self::assertStringContainsString("'''raw'''", $page->html);
        self::assertFalse($page->visualEditable);
    }

    public function testARedirectLinksItsTarget(): void
    {
        self::assertStringContainsString('→ <a href="/wiki/Hosting_tips"', $this->parse('#REDIRECT [[Hosting tips]]')->html);
    }

    public function testHeadingIdsAreUnique(): void
    {
        $page = $this->parse("== Tips ==\n== Tips ==\n== Ölçü ==");

        self::assertSame(['wiki-tips', 'wiki-tips-2', 'wiki-l'], array_column($page->headings, 'id'));
    }

    public function testWhatTheVisualEditorKeeps(): void
    {
        self::assertTrue(WikiParser::isVisualEditable("== H ==\n'''b''' [[Page|text]] [https://x.org x]\n* a\n# b\n; term\n: description\n----\n[[Category:C]]"));
        foreach (['{{x}}', "{|\n|}", '<br>', ' pre', ':: indent', ';* list', '** nested', '===== h5 =====', '[[Image:a.png]]', '__TOC__', '#REDIRECT [[X]]'] as $markup) {
            self::assertFalse(WikiParser::isVisualEditable($markup), $markup);
        }
    }

    public function testTheFirstMarkupTheVisualEditorCantKeep(): void
    {
        self::assertNull(WikiParser::visualBlocker("; Option\n: Text"));
        self::assertSame(['what' => 'nested', 'line' => 3], WikiParser::visualBlocker("Intro\r\n\r\n** deep\n{{later}}"));
        self::assertSame(['what' => 'template', 'line' => 1], WikiParser::visualBlocker('{{x}} <b>y</b>'));
    }

    public function testPlainText(): void
    {
        self::assertSame(
            'Hosting Read the bold request and Example. Item',
            WikiParser::plainText("== Hosting ==\nRead the '''bold''' [[Requests|request]] and [https://x.org Example].\n* Item\n[[Category:Hosting]]"),
        );
    }

    public function testPageNames(): void
    {
        self::assertSame('Hosting_tips', WikiPageName::fromTitle(' Hosting tips '));
        self::assertSame('Group_HikingintheAlps', WikiPageName::fromTitle('Group Hiking in the Alps'));
        self::assertSame('Group_HikingintheAlps', WikiPageName::forGroup('Hiking in the Alps'));
    }

    private function parse(string $markup): ParsedWikiPage
    {
        $resolver = new class implements WikiLinkResolverInterface {
            public function resolve(array $titles): array
            {
                $links = [];
                foreach ($titles as $title) {
                    $name = WikiPageName::fromTitle($title);
                    $links[$title] = match ($name) {
                        'Hosting_tips' => new WikiLink(WikiLink::PAGE, $name, '/wiki/Hosting_tips'),
                        'Group_Lisbon' => new WikiLink(WikiLink::GROUP, $name, '/group/7/wiki'),
                        default => new WikiLink(WikiLink::PAGE, $name, '/wiki/' . $name . '/create', false),
                    };
                }

                return $links;
            }
        };
        $translator = new class implements TranslatorInterface {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return $id;
            }

            public function getLocale(): string
            {
                return 'en';
            }
        };

        return new WikiParser($resolver, $translator, static::getContainer()->get(UrlGeneratorInterface::class))->parse($markup);
    }
}
