<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The safety pages: the tabs with the open one marked, the report button, and the anchors other pages link to
 * (the terms link to #external, the overview to the tips' #general and #female).
 */
#[Group('integration')]
final class SafetyPagesTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function pages(): iterable
    {
        yield 'overview' => ['/safety', ['trustworthy', 'uncomfortable', 'emergency', 'external', 'contact', 'keepitsafe', 'tips']];
        yield 'basics' => ['/safety/basics', ['trustworthy']];
        yield 'what to do' => ['/safety/whattodo', ['emergency', 'uncomfortable']];
        yield 'tips' => ['/safety/tips', ['general', 'female']];
        yield 'faq' => ['/safety/faq', ['general', 'comments', 'spam']];
        yield 'team' => ['/safety/team', ['tasks', 'proactive', 'reactive', 'confidentiality', 'members']];
    }

    /**
     * @param list<string> $anchors
     */
    #[DataProvider('pages')]
    public function testAPageHasItsTabAndAnchors(string $path, array $anchors): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        $this->assertResponseIsSuccessful();
        self::assertCount(6, $crawler->filter('.p-safety__tab'));
        self::assertSame($path, $crawler->filter('.p-safety__tab.is-active')->attr('href'));
        self::assertSame('/feedback?IdCategory=2', $crawler->filter('.p-safety__report')->attr('href'));
        foreach ($anchors as $anchor) {
            self::assertCount(1, $crawler->filter('#' . $anchor), $anchor);
        }
        // the texts bring their own paragraphs and lists: never a paragraph in a paragraph
        self::assertCount(0, $crawler->filter('p p, p ul, p ol'));
    }

    public function testTheTeamPageListsTheTeamOnce(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/safety/team');

        // bwadmin is in the Safety Team in the fixtures
        self::assertCount(1, $crawler->filter('.p-safety__members a[href="/members/bwadmin"]'));
        // the reactive work was shown twice before
        self::assertCount(1, $crawler->filter('#reactive .p-safety__text'));
    }
}
