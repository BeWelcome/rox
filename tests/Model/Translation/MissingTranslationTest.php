<?php

namespace App\Tests\Model\Translation;

use App\Doctrine\TranslationAllowedType;
use App\Doctrine\TranslationFormat;
use App\Model\Translation\MissingTranslation;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MissingTranslationTest extends TestCase
{
    public function testAListGivesTextDescriptionDomainAndWhetherToTranslate(): void
    {
        $entry = MissingTranslation::fromYaml('code', ['Text', 'Description', 'messages', 'yes']);

        self::assertSame('Text', $entry->text);
        self::assertSame('Description', $entry->description);
        self::assertSame('messages', $entry->domain);
        self::assertSame(TranslationAllowedType::TRANSLATION_NOT_ALLOWED, $entry->translationAllowed);
        self::assertNull($entry->format);
    }

    public function testAShortListHasTheDefaults(): void
    {
        $entry = MissingTranslation::fromYaml('code', ['Text']);

        self::assertSame('No description given.', $entry->description);
        self::assertSame('messages+intl-icu', $entry->domain);
        self::assertSame(TranslationAllowedType::TRANSLATION_ALLOWED, $entry->translationAllowed);
    }

    public function testAMapAlsoGivesTheFormat(): void
    {
        $entry = MissingTranslation::fromYaml('code', [
            'text' => 'Read the <a href="/r">rules</a>',
            'description' => 'Description',
            'translate' => false,
            'format' => 'inline',
        ]);

        self::assertSame('Read the <a href="/r">rules</a>', $entry->text);
        self::assertSame('messages+intl-icu', $entry->domain);
        self::assertSame(TranslationAllowedType::TRANSLATION_NOT_ALLOWED, $entry->translationAllowed);
        self::assertSame(TranslationFormat::Inline, $entry->format);
    }

    public function testAnUnknownFormatIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('code: format "bold" is none of html, inline, plain.');

        MissingTranslation::fromYaml('code', ['text' => 'Text', 'format' => 'bold']);
    }

    public function testAnUnknownNameIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not formats');

        MissingTranslation::fromYaml('code', ['text' => 'Text', 'formats' => 'plain']);
    }
}
