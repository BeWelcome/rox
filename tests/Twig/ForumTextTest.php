<?php

namespace App\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * The forum_text filter: HTML posts keep their paragraphs without the empty gaps, plain posts get line breaks.
 */
final class ForumTextTest extends KernelTestCase
{
    public function testHtmlPostsLoseEmptyParagraphsAndGetNoExtraBreaks(): void
    {
        $text = $this->forumText("<p>One</p>\n\n<p>&nbsp;</p>\n<p><br></p>\n<p>Two</p>");

        self::assertStringNotContainsString('<br', $text);
        self::assertStringNotContainsString('&nbsp;', $text);
        self::assertStringContainsString('<p>One</p>', $text);
        self::assertStringContainsString('<p>Two</p>', $text);
    }

    public function testPlainPostsKeepAtMostOneEmptyLine(): void
    {
        self::assertSame("one<br />\n<br />\ntwo", $this->forumText("one\n\n\n\n\ntwo"));
    }

    private function forumText(string $text): string
    {
        /** @var Environment $twig */
        $twig = static::getContainer()->get('twig');

        return $twig->createTemplate('{{ text|forum_text }}')->render(['text' => $text]);
    }
}
