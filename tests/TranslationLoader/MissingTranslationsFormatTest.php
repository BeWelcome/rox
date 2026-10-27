<?php

namespace App\Tests\TranslationLoader;

use MessageFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Every text in translations/missing (imported by translations:add:missing, ICU by default) can be formatted.
 *
 * ICU accepts some broken texts as patterns and only fails when formatting, on the page: an argument used as a
 * plural and again as {name} inside it ("Inconsistent types declared for an argument", activities.empty.near).
 * translations:add:missing never changes a text once it is in the database: fix one there in the translation tool.
 */
class MissingTranslationsFormatTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function texts(): iterable
    {
        foreach (glob(__DIR__ . '/../../translations/missing/*.yaml') as $file) {
            foreach (Yaml::parseFile($file) ?? [] as $key => $value) {
                $text = \is_array($value) ? $value[0] : $value;
                $domain = \is_array($value) ? ($value[2] ?? 'messages+intl-icu') : 'messages+intl-icu';
                if (\is_string($text) && str_contains($text, '{') && str_ends_with($domain, '+intl-icu')) {
                    yield basename($file) . ' ' . $key => [$text];
                }
            }
        }
    }

    #[DataProvider('texts')]
    public function testTheTextFormatsWithItsArguments(string $text): void
    {
        // every argument as a number: plural, select by number and simple arguments all accept it
        preg_match_all('/\{\s*([A-Za-z_][A-Za-z0-9_]*)\s*[,}]/', $text, $arguments);
        $formatter = MessageFormatter::create('en', $text);
        self::assertNotNull($formatter, 'not an ICU pattern: ' . intl_get_error_message());

        $formatted = $formatter->format(array_fill_keys(array_unique($arguments[1]), 3));

        self::assertNotFalse($formatted, $formatter->getErrorMessage());
    }
}
