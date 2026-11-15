<?php

namespace App\Tests\Templates;

use DateTime;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The posts of the personal data export show as in the forum (user_text): an HTML post keeps its paragraphs without a
 * <br> for every line break of its source, an old plain text post gets its line breaks.
 */
#[Group('integration')]
class MyDataPostTextTest extends KernelTestCase
{
    public function testHtmlPostsGetNoLineBreaksAndPlainOnesKeepTheirs(): void
    {
        self::bootKernel();
        $twig = static::getContainer()->get('twig');
        $twig->disableStrictVariables();
        $post = static fn (int $id, string $message): array => [
            'id' => $id,
            'thread' => null,
            'author' => ['username' => 'member-2', 'activeAddress' => false],
            'created' => new DateTime('2026-11-01 10:00'),
            'PostVisibility' => 'NoRestriction',
            'messageTranslations' => ['en' => ['language' => ['shortCode' => 'en'], 'message' => $message]],
        ];

        $html = $twig->render('private/posts.html.twig', ['posts' => [
            $post(1, "<p>First paragraph.</p>\n<p>Second paragraph.</p>\n"),
            $post(2, "First line\nSecond line"),
        ]]);

        $texts = new Crawler($html)->filter('.c-mydata-card__text');
        self::assertCount(2, $texts);
        self::assertStringNotContainsString('<br', $texts->eq(0)->html());
        self::assertCount(2, $texts->eq(0)->filter('p'));
        self::assertCount(1, $texts->eq(1)->filter('br'));
    }
}
