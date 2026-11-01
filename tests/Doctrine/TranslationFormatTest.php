<?php

namespace App\Tests\Doctrine;

use App\Doctrine\TranslationFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TranslationFormatTest extends TestCase
{
    /**
     * @return iterable<string, array{string, TranslationFormat}>
     */
    public static function texts(): iterable
    {
        yield 'text' => ['Search words', TranslationFormat::Plain];
        yield 'text with an ICU argument' => ['{count, plural, one {# reply} other {# replies}}', TranslationFormat::Plain];
        yield 'a link' => ['Read the <a href="/rules">rules</a> first.', TranslationFormat::Inline];
        yield 'bold and a line break' => ['<strong>Note:</strong><br>later', TranslationFormat::Inline];
        yield 'a paragraph' => ['<p>Welcome</p>', TranslationFormat::Html];
        yield 'a list' => ['Pick one:<ul><li>a</li></ul>', TranslationFormat::Html];
        yield 'a heading' => ['<h2>Rules</h2>', TranslationFormat::Html];
    }

    #[DataProvider('texts')]
    public function testIsDetectedFromTheText(string $text, TranslationFormat $format): void
    {
        self::assertSame($format, TranslationFormat::detect($text));
    }

    public function testOnlyPlainTextIsWrittenWithoutTheEditor(): void
    {
        self::assertTrue(TranslationFormat::Html->usesEditor());
        self::assertTrue(TranslationFormat::Inline->usesEditor());
        self::assertFalse(TranslationFormat::Plain->usesEditor());
    }

    /**
     * @return iterable<string, array{TranslationFormat, string, string}>
     */
    public static function written(): iterable
    {
        yield 'inline: the editor\'s paragraph is taken off' => [TranslationFormat::Inline, '<p>Read the <a href="/r">rules</a></p>', 'Read the <a href="/r">rules</a>'];
        yield 'inline: paragraphs become lines, empty ones go' => [TranslationFormat::Inline, "<p>one</p>\n<p>&nbsp;</p><p class=\"x\">two</p>", 'one<br>two'];
        yield 'inline: other markup is kept' => [TranslationFormat::Inline, '<p>a</p><ul><li>b</li></ul><p>c</p>', '<p>a</p><ul><li>b</li></ul><p>c</p>'];
        yield 'inline: written without paragraphs' => [TranslationFormat::Inline, 'Read <b>this</b>', 'Read <b>this</b>'];
        yield 'html: kept as it is' => [TranslationFormat::Html, '<p>one</p><p>two</p>', '<p>one</p><p>two</p>'];
        yield 'plain: from the editor, the text alone' => [TranslationFormat::Plain, '<p>Fish &amp; <b>chips</b></p><p>line&nbsp;two<br>three</p>', "Fish & chips\nline two\nthree"];
        yield 'plain: typed, kept as it is' => [TranslationFormat::Plain, 'I <3 hosting', 'I <3 hosting'];
    }

    #[DataProvider('written')]
    public function testIsStoredSoThatItFitsItsPlace(TranslationFormat $format, string $written, string $stored): void
    {
        self::assertSame($stored, $format->prepare($written));
    }
}
