<?php

namespace App\Doctrine;

/**
 * How a translatable text is used on the page, so that a translation fits into its place:
 * - Html: a block of HTML (paragraphs, lists), written with the rich text editor;
 * - Inline: part of a sentence or element in the page's HTML, with inline markup (links, bold) but no paragraph of its
 *   own: the paragraph the rich text editor puts around the text is taken off;
 * - Plain: text without any markup, written in a plain text field.
 *
 * Set on the English text (word.format); translations follow it. Not set, it is detected from the English text.
 */
enum TranslationFormat: string
{
    case Html = 'html';
    case Inline = 'inline';
    case Plain = 'plain';

    /** Elements that make a text a block of HTML */
    private const string BLOCK_ELEMENTS = 'p|div|ul|ol|li|h[1-6]|blockquote|table|figure|hr|pre|dl';

    public static function detect(string $text): self
    {
        if (1 === preg_match('~<\s*(?:' . self::BLOCK_ELEMENTS . ')\b~i', $text)) {
            return self::Html;
        }

        return $text === strip_tags($text) ? self::Plain : self::Inline;
    }

    /**
     * Written with the rich text editor (html, inline) or in a plain text field.
     */
    public function usesEditor(): bool
    {
        return self::Plain !== $this;
    }

    /**
     * The text as it is stored. Written in the rich text editor (its text is in paragraphs): for inline texts without
     * them, several paragraphs become lines (<br>); for plain texts the text alone, paragraphs and lines as line
     * breaks. Anything else is kept as it was written.
     */
    public function prepare(string $text): string
    {
        $paragraphs = self::Html === $this ? null : self::editorParagraphs($text);
        if (null === $paragraphs) {
            return $text;
        }

        if (self::Inline === $this) {
            return implode('<br>', $paragraphs);
        }

        $lines = array_map(
            static fn (string $paragraph): string => html_entity_decode(
                strip_tags((string) preg_replace('~<br\s*/?>~i', "\n", $paragraph)),
                \ENT_QUOTES | \ENT_HTML5,
                'UTF-8'
            ),
            $paragraphs
        );

        return trim(str_replace("\u{a0}", ' ', implode("\n", $lines)));
    }

    /**
     * The paragraphs of a text written in the rich text editor (one or more <p> elements and nothing else), without
     * the empty ones; null for any other text.
     *
     * @return list<string>|null
     */
    private static function editorParagraphs(string $text): ?array
    {
        $text = trim($text);
        if (1 !== preg_match('~^<p(?:\s[^>]*)?>.*</p>$~is', $text)) {
            return null;
        }

        $paragraphs = preg_split('~</p>\s*<p(?:\s[^>]*)?>~i', $text);
        $paragraphs[0] = (string) preg_replace('~^<p(?:\s[^>]*)?>~i', '', $paragraphs[0]);
        $last = \count($paragraphs) - 1;
        $paragraphs[$last] = (string) preg_replace('~</p>$~i', '', $paragraphs[$last]);
        // a paragraph in the middle that holds a <p> of its own: not just paragraphs
        foreach ($paragraphs as $paragraph) {
            if (1 === preg_match('~</?p[\s>]~i', $paragraph)) {
                return null;
            }
        }

        // an empty paragraph (an empty line in the editor) is no line
        return array_values(array_filter(
            array_map('trim', $paragraphs),
            static fn (string $paragraph): bool => !\in_array($paragraph, ['', '&nbsp;', '<br>'], true)
        ));
    }
}
