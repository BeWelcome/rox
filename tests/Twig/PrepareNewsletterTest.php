<?php

namespace App\Tests\Twig;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * The prepare_newsletter filter keeps the headings of a newsletter and drops what isn't allowed. It translates the
 * alt text of images, so it needs the database's translations.
 */
#[Group('integration')]
final class PrepareNewsletterTest extends KernelTestCase
{
    public function testHeadingsStayAndOtherTagsGo(): void
    {
        $text = $this->prepare('<h1>Title</h1><h2>Section</h2><h3>Part</h3><h4>Small</h4><p>Text</p><script>alert(1)</script>');

        self::assertStringContainsString('<h2>Section</h2>', $text);
        self::assertStringContainsString('<h3>Part</h3>', $text);
        self::assertStringContainsString('<p>Text</p>', $text);
        self::assertStringNotContainsString('<h1', $text);
        self::assertStringNotContainsString('<h4', $text);
        self::assertStringNotContainsString('script', $text);
    }

    private function prepare(string $text): string
    {
        /** @var Environment $twig */
        $twig = static::getContainer()->get('twig');

        return $twig->createTemplate('{{ text|prepare_newsletter(true) }}')->render(['text' => $text]);
    }
}
