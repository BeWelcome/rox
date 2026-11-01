<?php

namespace App\Model\Translation;

use App\Doctrine\TranslationAllowedType;
use App\Doctrine\TranslationFormat;
use InvalidArgumentException;

/**
 * A text of translations/missing/*.yaml, for translations:add:missing. Either a list:
 *
 *     code:
 *         - 'The English text'
 *         - Description (optional)
 *         - domain (optional, messages+intl-icu)
 *         - 'yes' to not translate it (optional, 'no')
 *
 * or with names, which also takes the format (TranslationFormat: html, inline, plain; detected from the text if
 * left out):
 *
 *     code:
 *         text: 'The English text'
 *         description: Description
 *         domain: messages
 *         translate: false
 *         format: inline
 */
final readonly class MissingTranslation
{
    private const string NO_DESCRIPTION = 'No description given.';
    private const string DEFAULT_DOMAIN = 'messages+intl-icu';
    private const array NAMES = ['text', 'description', 'domain', 'translate', 'format'];

    private function __construct(
        public string $text,
        public string $description,
        public string $domain,
        /** TranslationAllowedType: the database stores whether not to translate it */
        public string $translationAllowed,
        public ?TranslationFormat $format,
    ) {
    }

    /**
     * @throws InvalidArgumentException for an entry that isn't one of the two forms
     */
    public static function fromYaml(string $code, mixed $entry): self
    {
        if (\is_string($entry)) {
            $entry = [$entry];
        }
        if (!\is_array($entry) || [] === $entry) {
            throw new InvalidArgumentException(\sprintf('%s: a text, a list or a map with "text" is expected.', $code));
        }

        if (array_is_list($entry)) {
            return new self(
                (string) $entry[0],
                (string) ($entry[1] ?? self::NO_DESCRIPTION),
                (string) ($entry[2] ?? self::DEFAULT_DOMAIN),
                // the database's column says whether NOT to translate it
                'yes' === ($entry[3] ?? 'no') ? TranslationAllowedType::TRANSLATION_NOT_ALLOWED : TranslationAllowedType::TRANSLATION_ALLOWED,
                null,
            );
        }

        $unknown = array_diff(array_keys($entry), self::NAMES);
        if ([] !== $unknown || !isset($entry['text'])) {
            throw new InvalidArgumentException(\sprintf('%s: "text" is needed; known are %s, not %s.', $code, implode(', ', self::NAMES), implode(', ', $unknown) ?: '-'));
        }

        $format = null;
        if (isset($entry['format'])) {
            $format = TranslationFormat::tryFrom((string) $entry['format']);
            if (null === $format) {
                throw new InvalidArgumentException(\sprintf('%s: format "%s" is none of %s.', $code, $entry['format'], implode(', ', array_map(static fn (TranslationFormat $case): string => $case->value, TranslationFormat::cases()))));
            }
        }

        return new self(
            (string) $entry['text'],
            (string) ($entry['description'] ?? self::NO_DESCRIPTION),
            (string) ($entry['domain'] ?? self::DEFAULT_DOMAIN),
            false === ($entry['translate'] ?? true) ? TranslationAllowedType::TRANSLATION_NOT_ALLOWED : TranslationAllowedType::TRANSLATION_ALLOWED,
            $format,
        );
    }
}
