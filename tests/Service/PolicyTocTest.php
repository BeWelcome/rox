<?php

namespace App\Tests\Service;

use App\Service\PolicyToc;
use PHPUnit\Framework\TestCase;

final class PolicyTocTest extends TestCase
{
    public function testSectionsGiveTheListAndTheTextStays(): void
    {
        $html = '<section id="introduction"><p>Hello</p></section><section id="cookies"><h2>Cookies <em>here</em></h2><p>Some</p></section>';

        $toc = new PolicyToc()->build($html, 'Introduction');

        self::assertSame($html, $toc['html']);
        self::assertSame([['id' => 'introduction', 'label' => 'Introduction'], ['id' => 'cookies', 'label' => 'Cookies here']], $toc['items']);
    }

    public function testHeadingsOfATranslationGetIds(): void
    {
        $toc = new PolicyToc()->build('<p>Intro</p><h3>Uno</h3><p>a</p><h2 id="dos">Dos</h2><h3></h3><h3>Tres</h3>', 'Introduction');

        self::assertSame([['id' => 'section-1', 'label' => 'Uno'], ['id' => 'dos', 'label' => 'Dos'], ['id' => 'section-2', 'label' => 'Tres']], $toc['items']);
        self::assertStringContainsString('<h3 id="section-1">Uno</h3>', $toc['html']);
        self::assertStringContainsString('<h3 id="section-2">Tres</h3>', $toc['html']);
    }

    public function testATextWithoutHeadingsHasNoList(): void
    {
        $toc = new PolicyToc()->build('<p>Only text</p>', 'Introduction');

        self::assertSame([], $toc['items']);
        self::assertSame('<p>Only text</p>', $toc['html']);
    }
}
