<?php

namespace App\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * The user_text filter: HTML texts keep their paragraphs without the empty gaps, plain texts get line breaks.
 */
final class UserTextTest extends KernelTestCase
{
    public function testHtmlPostsLoseEmptyParagraphsAndGetNoExtraBreaks(): void
    {
        $text = $this->userText("<p>One</p>\n\n<p>&nbsp;</p>\n<p><br></p>\n<p>Two</p>");

        self::assertStringNotContainsString('<br', $text);
        self::assertStringNotContainsString('&nbsp;', $text);
        self::assertStringContainsString('<p>One</p>', $text);
        self::assertStringContainsString('<p>Two</p>', $text);
    }

    public function testPlainPostsKeepAtMostOneEmptyLine(): void
    {
        self::assertSame("one<br />\n<br />\ntwo", $this->userText("one\n\n\n\n\ntwo"));
    }

    private function userText(string $text): string
    {
        /** @var Environment $twig */
        $twig = static::getContainer()->get('twig');

        return $twig->createTemplate('{{ text|user_text }}')->render(['text' => $text]);
    }
}
