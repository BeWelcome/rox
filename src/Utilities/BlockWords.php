<?php

namespace App\Utilities;

use Normalizer;

/**
 * The words that hold a message back for the checkers (ConversationModel::formatConversation): e-mail addresses and
 * words spammers use. The checkers see them marked in the message and listed next to it.
 */
final class BlockWords
{
    public const string PATTERN = '/@|\.at\.|-at-|\(at\)|verif|\.shop|system|support/i';

    /**
     * @return list<string> the block words in the text, each once, lower case
     */
    public static function found(string $text): array
    {
        preg_match_all(self::PATTERN, self::normalize(html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5)), $matches);

        return array_values(array_unique(array_map(mb_strtolower(...), $matches[0])));
    }

    /**
     * Marks the block words in (purified) HTML, in its text only, not in tags or attributes.
     */
    public static function mark(string $html): string
    {
        $parts = preg_split('/(<[^>]*>)/', $html, -1, \PREG_SPLIT_DELIM_CAPTURE) ?: [$html];
        foreach ($parts as $index => $part) {
            if ('' !== $part && '<' !== $part[0]) {
                $parts[$index] = preg_replace(self::PATTERN, '<mark>$0</mark>', $part) ?? $part;
            }
        }

        return implode('', $parts);
    }

    /** without the invisible characters and with compatibility forms folded (ｓｕｐｐｏｒｔ is support) */
    public static function normalize(string $text): string
    {
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text) ?? $text;
        $normalized = Normalizer::normalize($text, Normalizer::FORM_KC);

        return false === $normalized ? $text : $normalized;
    }
}
