<?php

namespace App\Tests\Twig;

use App\Twig\Extension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The forum_text filter: HTML posts keep their paragraphs without the empty gaps, plain posts get line breaks.
 */
final class ForumTextTest extends KernelTestCase
{
    public function testHtmlPostsLoseEmptyParagraphsAndGetNoExtraBreaks(): void
    {
        $text = $this->extension()->forumText("<p>One</p>\n\n<p>&nbsp;</p>\n<p><br></p>\n<p>Two</p>");

        self::assertStringNotContainsString('<br', $text);
        self::assertStringNotContainsString('&nbsp;', $text);
        self::assertStringContainsString('<p>One</p>', $text);
        self::assertStringContainsString('<p>Two</p>', $text);
    }

    public function testPlainPostsKeepAtMostOneEmptyLine(): void
    {
        self::assertSame("one<br />\n<br />\ntwo", $this->extension()->forumText("one\n\n\n\n\ntwo"));
    }

    private function extension(): Extension
    {
        return static::getContainer()->get(Extension::class);
    }
}
