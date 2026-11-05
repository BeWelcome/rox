<?php

namespace App\Wiki;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Turns wiki markup into HTML. The markup of the editor's toolbar:
 *
 *     == H2 ==  === H3 ===  ==== H4 ====  '''bold'''  ''italic''  [[Page]]  [[Page|text]]  [https://url text]
 *     * bullet  # numbered  ----  [[Category:Name]] (pulled out and returned as categories)
 *
 * and what older pages use: = H1 = (shown as H2), H5 and H6, nested lists, ; term : definition, : indents, lines
 * starting with a space (preformatted), {| tables |}, HTML, <nowiki>, {{templates}} (member links, else their
 * name), images (their caption), magic words like __TOC__ (dropped). Bare addresses become links.
 *
 * The HTML still needs the purifier's wiki profile (templates use |purify('wiki')): HTML in the markup passes
 * through here.
 *
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 */
final class WikiParser
{
    /** HTML tags that pass through; the purifier drops their unsafe attributes */
    private const string HTML_TAGS = 'a|abbr|b|big|blockquote|br|center|code|del|div|em|font|h[1-6]|hr|i|img|ins|kbd|li|ol|p|pre|s|small|span|strike|strong|sub|sup|table|tbody|td|tfoot|th|thead|tr|tt|u|ul';

    /**
     * What the visual editor can't keep, by what the note names (wiki.editor.blocker.*). The visual editor keeps the
     * toolbar's markup, single-level lists and definition lists (; term, : description). The editor's script checks
     * the same (assets/js/wiki/markup.js, UNSUPPORTED).
     */
    private const array VISUAL_BLOCKERS = [
        'template' => '~\{\{~',
        'table' => '~\{\|~',
        'html' => '~</?[a-z][^>]*>|<!--~i',
        'magic' => '~__[A-Z]+__~',
        'redirect' => '~^#redirect~mi',
        'preformatted' => '~^[ \t]+\S~m',
        'nested' => '~^[*#]{2,}|^[*#]+[;:]|^[;:][;:*#]~m',
        'heading' => '~^={5,}~m',
        'image' => '~\[\[\s*(?:image|file|media)\s*:~i',
    ];

    /** @var array<string, string> rendered pieces the escaping must not touch */
    private array $protected = [];

    /** @var array<string, WikiLink> by title as written */
    private array $resolved = [];

    /** @var array<string, WikiLink> the links of the page, by kind and target */
    private array $links = [];

    /** @var list<array{level: int, text: string, id: string}> */
    private array $headings = [];

    /** @var array<string, true> */
    private array $ids = [];

    public function __construct(
        private readonly WikiLinkResolverInterface $resolver,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Whether the visual editor shows the markup without losing anything.
     */
    public static function isVisualEditable(string $markup): bool
    {
        return null === self::visualBlocker($markup);
    }

    /**
     * The first markup the visual editor can't keep: what it is (a key of VISUAL_BLOCKERS) and its line.
     *
     * @return array{what: string, line: int}|null
     */
    public static function visualBlocker(string $markup): ?array
    {
        $markup = str_replace("\r", '', $markup);
        $first = null;
        foreach (self::VISUAL_BLOCKERS as $what => $pattern) {
            if (preg_match($pattern, $markup, $match, \PREG_OFFSET_CAPTURE) && (null === $first || $match[0][1] < $first[1])) {
                $first = [$what, $match[0][1]];
            }
        }

        return null === $first ? null : ['what' => $first[0], 'line' => substr_count($markup, "\n", 0, $first[1]) + 1];
    }

    public function parse(string $markup): ParsedWikiPage
    {
        $this->protected = [];
        $this->links = [];
        $this->headings = [];
        $this->ids = [];

        $text = str_replace(["\r\n", "\r"], "\n", $markup);
        $text = preg_replace('~<!--.*?(-->|$)~s', '', $text);
        // scripts, styles and frames never show, not even as text
        $text = preg_replace('~<(script|style|iframe)\b[^>]*>.*?(</\1\s*>|$)~si', '', $text);
        $text = $this->protectNowikiAndPre($text);
        [$text, $categories] = self::splitCategories($text);
        $text = preg_replace('~__[A-Z]+__~', '', $text);
        // a redirect of the old wiki: a link to its target
        $text = preg_replace('~^\s*#redirect\s*(\[\[[^\]]+\]\])~i', '→ $1', $text);

        preg_match_all('~\[\[([^\[\]|]+)(?:\|[^\[\]]*)?\]\]~', $text, $matches);
        $titles = array_values(array_unique(array_map('trim', array_filter(
            $matches[1],
            static fn (string $title) => !preg_match('~^\s*:?\s*(category|image|file|media)\s*:|://~i', $title),
        ))));
        $this->resolved = [] === $titles ? [] : $this->resolver->resolve($titles);

        $html = $this->restore($this->blocks(explode("\n", $text)));

        return new ParsedWikiPage(
            $html,
            $this->headings,
            $categories,
            array_values($this->links),
            self::isVisualEditable($markup),
        );
    }

    /**
     * The categories of a page ([[Category:Name]]).
     *
     * @return list<string>
     */
    public static function categoriesOf(string $markup): array
    {
        return self::splitCategories(str_replace("\r", '', $markup))[1];
    }

    /**
     * Plain text of a page, for excerpts and search results: no markup, categories, templates or HTML.
     */
    public static function plainText(string $markup): string
    {
        $text = self::splitCategories(str_replace("\r", '', $markup))[0];
        $text = preg_replace('~\{\{[^{}]*\}\}|<!--.*?-->|__[A-Z]+__~s', ' ', $text);
        $text = preg_replace('~\[\[(?:[^\[\]|]*\|)?([^\[\]]*)\]\]~', '$1', $text);
        $text = preg_replace('~\[(?:https?|ftp)://\S+\s+([^\]]*)\]~i', '$1', $text);
        $text = preg_replace("~'{2,}|^[=*#;:]+|=+\s*$|^-{4,}|\{\||\|\}|^\s*[|!]-?~m", '', $text);
        $text = html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5);

        return trim((string) preg_replace('~\s+~u', ' ', $text));
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    private static function splitCategories(string $text): array
    {
        $categories = [];
        $text = preg_replace_callback(
            '~^[ \t]*\[\[\s*category\s*:\s*([^\]|]+?)\s*(?:\|[^\]]*)?\]\][ \t]*(\n|$)|\[\[\s*category\s*:\s*([^\]|]+?)\s*(?:\|[^\]]*)?\]\]~mi',
            static function (array $match) use (&$categories): string {
                $name = str_replace('_', ' ', '' !== $match[1] ? $match[1] : $match[3]);
                if (!\in_array($name, $categories, true)) {
                    $categories[] = $name;
                }

                return '';
            },
            $text,
        );

        return [$text, $categories];
    }

    private function protectNowikiAndPre(string $text): string
    {
        $text = preg_replace_callback(
            '~<nowiki>(.*?)(</nowiki>|$)~si',
            fn (array $match) => $this->protect(htmlspecialchars($match[1], \ENT_NOQUOTES)),
            $text,
        );

        return preg_replace_callback(
            '~<pre(\s[^>]*)?>(.*?)(</pre>|$)~si',
            fn (array $match) => $this->protect('<pre>' . htmlspecialchars($match[2], \ENT_NOQUOTES, 'UTF-8', false) . '</pre>'),
            $text,
        );
    }

    /**
     * @param list<string> $lines
     *
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     */
    private function blocks(array $lines): string
    {
        $out = '';
        $paragraph = [];
        $preformatted = [];
        $listPrefix = '';
        $table = null;

        $flushParagraph = function () use (&$out, &$paragraph): void {
            $content = trim(implode("\n", $paragraph));
            if ('' !== $content) {
                $out .= '<p>' . $this->inline($content) . "</p>\n";
            }
            $paragraph = [];
        };
        $flushPreformatted = function () use (&$out, &$preformatted): void {
            if ([] !== $preformatted) {
                $out .= '<pre>' . implode("\n", array_map(fn (string $line) => $this->inline($line), $preformatted)) . "</pre>\n";
            }
            $preformatted = [];
        };
        $flushList = function () use (&$out, &$listPrefix): void {
            $out .= $this->closeLists($listPrefix, 0);
            $listPrefix = '';
        };
        $flushAll = static function () use ($flushParagraph, $flushPreformatted, $flushList): void {
            $flushParagraph();
            $flushPreformatted();
            $flushList();
        };

        foreach ($lines as $line) {
            if (null !== $table) {
                if (preg_match('~^\s*\|\}~', $line)) {
                    $out .= $this->table($table);
                    $table = null;
                } else {
                    $table[] = $line;
                }
                continue;
            }

            if (preg_match('~^\s*\{\|(.*)$~', $line, $match)) {
                $flushAll();
                $table = [$match[1]];
                continue;
            }

            if ('' === trim($line)) {
                $flushAll();
                continue;
            }

            if (preg_match('~^(={1,6})\s*(.+?)\s*\1\s*$~', $line, $match)) {
                $flushAll();
                $out .= $this->heading(\strlen($match[1]), $match[2]);
                continue;
            }

            if (preg_match('~^-{4,}\s*$~', $line)) {
                $flushAll();
                $out .= "<hr>\n";
                continue;
            }

            if (preg_match('~^([*#;:]+)\s*(.*)$~', $line, $match)) {
                $flushParagraph();
                $flushPreformatted();
                [$prefix, $content] = [$match[1], $match[2]];
                // ; term : definition on one line
                if (str_ends_with($prefix, ';') && preg_match('~^(.*?)\s:\s(.*)$~', $content, $definition)) {
                    $out .= $this->listItem($listPrefix, $prefix, $definition[1]);
                    $prefix = substr($prefix, 0, -1) . ':';
                    $content = $definition[2];
                }
                $out .= $this->listItem($listPrefix, $prefix, $content);
                continue;
            }

            if (preg_match('~^[ \t]+\S~', $line)) {
                $flushParagraph();
                $flushList();
                $preformatted[] = substr($line, 1);
                continue;
            }

            $flushPreformatted();
            $flushList();
            $paragraph[] = $line;
        }

        if (null !== $table) {
            $out .= $this->table($table);
        }
        $flushAll();

        return $out;
    }

    private function heading(int $depth, string $text): string
    {
        $level = max(2, $depth);
        $html = $this->inline($text);
        $plain = trim(html_entity_decode(strip_tags($this->restore($html)), \ENT_QUOTES | \ENT_HTML5));
        $id = $this->headingId($plain);
        if ($level <= 3) {
            $this->headings[] = ['level' => $level, 'text' => $plain, 'id' => $id];
        }

        return \sprintf("<h%d id=\"%s\">%s</h%d>\n", $level, $id, $html, $level);
    }

    private function headingId(string $text): string
    {
        $slug = trim((string) preg_replace('~[^a-z0-9]+~', '-', strtolower($text)), '-');
        $base = 'wiki-' . ('' === $slug ? 'section' : $slug);
        $id = $base;
        for ($i = 2; isset($this->ids[$id]); ++$i) {
            $id = $base . '-' . $i;
        }
        $this->ids[$id] = true;

        return $id;
    }

    /**
     * The next item of a list: closes the deeper lists of the previous item, opens the new ones (; and : share a
     * definition list).
     */
    private function listItem(string &$previous, string $prefix, string $content): string
    {
        $common = 0;
        $max = min(\strlen($previous), \strlen($prefix));
        while ($common < $max && $this->listTag($previous[$common]) === $this->listTag($prefix[$common])) {
            ++$common;
        }

        $out = $this->closeLists($previous, $common);
        if ($common === \strlen($prefix) && $common > 0) {
            $out .= '</' . $this->itemTag($previous[$common - 1]) . '><' . $this->itemTag($prefix[$common - 1]) . '>';
        }
        for ($i = $common, $length = \strlen($prefix); $i < $length; ++$i) {
            $out .= '<' . $this->listTag($prefix[$i]) . '><' . $this->itemTag($prefix[$i]) . '>';
        }
        $previous = $prefix;

        return $out . $this->inline(trim($content));
    }

    /** closes the lists of the prefix from the end down to the given depth */
    private function closeLists(string $prefix, int $depth): string
    {
        $out = '';
        for ($i = \strlen($prefix) - 1; $i >= $depth; --$i) {
            $out .= '</' . $this->itemTag($prefix[$i]) . '></' . $this->listTag($prefix[$i]) . ">\n";
        }

        return $out;
    }

    private function listTag(string $char): string
    {
        return match ($char) {
            '*' => 'ul',
            '#' => 'ol',
            default => 'dl',
        };
    }

    private function itemTag(string $char): string
    {
        return match ($char) {
            ';' => 'dt',
            ':' => 'dd',
            default => 'li',
        };
    }

    /**
     * {| attributes … |}: |+ caption, |- a new row, ! header cells, | cells; || and !! more cells on the line,
     * "attributes | content" in a cell.
     *
     * @param non-empty-list<string> $lines the table's attributes first
     */
    private function table(array $lines): string
    {
        $out = '<table' . $this->attributes(array_shift($lines)) . '>';
        $rows = [];
        $row = null;
        $caption = '';
        foreach ($lines as $line) {
            $line = ltrim($line);
            if (str_starts_with($line, '|+')) {
                $caption = $this->inline(trim(substr($line, 2)));
            } elseif (str_starts_with($line, '|-')) {
                if (null !== $row) {
                    $rows[] = $row;
                }
                $row = ['attributes' => $this->attributes(substr($line, 2)), 'cells' => []];
            } elseif (str_starts_with($line, '|') || str_starts_with($line, '!')) {
                $row ??= ['attributes' => '', 'cells' => []];
                $tag = str_starts_with($line, '!') ? 'th' : 'td';
                foreach (preg_split('~\|\||!!~', substr($line, 1)) as $cell) {
                    $parts = preg_split('~(?<!\|)\|(?!\|)~', $cell, 2);
                    $row['cells'][] = 2 === \count($parts) && !str_contains($parts[0], '[')
                        ? ['tag' => $tag, 'attributes' => $this->attributes($parts[0]), 'content' => $parts[1]]
                        : ['tag' => $tag, 'attributes' => '', 'content' => $cell];
                }
            } elseif (null !== $row && [] !== $row['cells']) {
                $row['cells'][\count($row['cells']) - 1]['content'] .= "\n" . $line;
            }
        }
        if (null !== $row) {
            $rows[] = $row;
        }

        if ('' !== $caption) {
            $out .= '<caption>' . $caption . '</caption>';
        }
        foreach ($rows as $row) {
            $out .= '<tr' . $row['attributes'] . '>';
            foreach ($row['cells'] as $cell) {
                $out .= \sprintf('<%1$s%2$s>%3$s</%1$s>', $cell['tag'], $cell['attributes'], $this->inline(trim($cell['content'])));
            }
            $out .= '</tr>';
        }

        return $out . "</table>\n";
    }

    /** a table's attributes as written; the purifier keeps the safe ones */
    private function attributes(string $attributes): string
    {
        $attributes = trim(str_replace(['<', '>'], '', $attributes));

        return '' === $attributes ? '' : ' ' . $attributes;
    }

    /**
     * Links, templates and HTML first (kept from the escaping), then the text escaped, bold and italic, bare
     * addresses.
     */
    private function inline(string $text): string
    {
        // templates, innermost first
        for ($i = 0; $i < 5 && str_contains($text, '{{'); ++$i) {
            $text = preg_replace_callback('~\{\{([^{}]*)\}\}~', fn (array $match) => $this->template($match[1]), $text);
        }

        // [[internal]] and [https://external] links in one pass, so the page's links keep their order
        $text = preg_replace_callback(
            '~\[\[([^\[\]|]+)(?:\|([^\[\]]*))?\]\]|\[((?:https?|ftp)://[^\s\[\]<>]+|mailto:[^\s\[\]<>]+)(?:\s+([^\]]*))?\]~i',
            fn (array $match) => '' !== $match[1]
                ? $this->internalLink(trim($match[1]), $match[2] ?? null)
                : $this->externalLink($match[3], $match[4] ?? null),
            $text,
        );
        $text = preg_replace_callback(
            '~</?(?:' . self::HTML_TAGS . ')(?:\s[^<>]*)?/?>~i',
            fn (array $match) => $this->protect($match[0]),
            $text,
        );

        $text = htmlspecialchars($text, \ENT_NOQUOTES, 'UTF-8', false);
        $text = preg_replace("~'''''(.+?)'''''~", '<strong><em>$1</em></strong>', $text);
        $text = preg_replace("~'''(.+?)'''~", '<strong>$1</strong>', $text);
        $text = preg_replace("~''(.+?)''~", '<em>$1</em>', $text);

        return preg_replace_callback(
            '~(?<![\w/="\'])((?:https?|ftp)://[^\s<>\[\]"\x01\x02]+?)(?=[.,;:!?)]*(?:\s|$|<|\x01))~i',
            fn (array $match) => $this->externalLink(html_entity_decode($match[1], \ENT_QUOTES | \ENT_HTML5), null),
            $text,
        );
    }

    private function internalLink(string $target, ?string $caption): string
    {
        $caption = null !== $caption && '' !== trim($caption) ? trim($caption) : null;

        // [[https://…]] in older pages
        if (preg_match('~^(?:https?|ftp)://~i', $target)) {
            return $this->externalLink($target, $caption);
        }

        // [[Image:name|…|caption]]: images aren't shown; their caption or name is
        if (preg_match('~^(?:image|file|media)\s*:\s*(.+)$~i', $target, $match)) {
            $parts = null === $caption ? [] : explode('|', $caption);

            return htmlspecialchars('' !== trim((string) end($parts)) ? trim((string) end($parts)) : $match[1], \ENT_NOQUOTES);
        }

        if (preg_match('~^:\s*category\s*:\s*(.+)$~i', $target, $match)) {
            $name = str_replace('_', ' ', trim($match[1]));
            $link = new WikiLink(WikiLink::CATEGORY, $name, $this->urlGenerator->generate('wiki_category', ['name' => $name]));
        } else {
            $link = $this->resolved[$target] ?? $this->resolved[trim($target, ':')] ?? null;
            if (null === $link) {
                return htmlspecialchars($caption ?? $target, \ENT_NOQUOTES);
            }
        }

        $label = $caption ?? str_replace('_', ' ', ltrim($target, ':'));
        $this->links[$link->kind . ':' . $link->target] ??= $link->withLabel($label);

        $classes = ['c-wiki-link'];
        $title = str_replace('_', ' ', $link->target);
        $icon = '';
        if (!$link->exists) {
            $classes[] = 'is-missing';
            $title = $this->translator->trans('wiki.link.missing');
        }
        if (WikiLink::GROUP === $link->kind) {
            $classes[] = 'is-group';
            $icon = '<i class="fas fa-users c-wiki-link__icon"></i>';
        }

        return $this->protect(\sprintf(
            '<a href="%s" class="%s" title="%s">%s%s</a>',
            htmlspecialchars($link->url),
            implode(' ', $classes),
            htmlspecialchars($title),
            $icon,
            $this->inlineLabel($label),
        ));
    }

    private function externalLink(string $url, ?string $caption): string
    {
        $label = null !== $caption && '' !== trim($caption) ? trim($caption) : $url;
        $link = new WikiLink(WikiLink::EXTERNAL, $url, $url, true, $label);
        $this->links[$link->kind . ':' . $url] ??= $link;

        return $this->protect(\sprintf(
            '<a href="%s" class="c-wiki-link is-external">%s<i class="fas fa-arrow-up-right-from-square c-wiki-link__icon"></i></a>',
            htmlspecialchars($url),
            $this->inlineLabel($label),
        ));
    }

    /** a link's text: escaped, bold and italic allowed */
    private function inlineLabel(string $label): string
    {
        $label = htmlspecialchars($label, \ENT_NOQUOTES, 'UTF-8', false);
        $label = preg_replace("~'''(.+?)'''~", '<strong>$1</strong>', $label);

        return preg_replace("~''(.+?)''~", '<em>$1</em>', $label);
    }

    /**
     * {{user|name}} and {{member|name|text}} link the member; other templates (from the old wiki) show their name.
     */
    private function template(string $content): string
    {
        $parts = array_map('trim', explode('|', $content));
        $name = strtolower($parts[0]);
        if (\in_array($name, ['user', 'member'], true) && isset($parts[1]) && preg_match('~^[\w.@-]+$~', $parts[1])) {
            return $this->protect(\sprintf(
                '<a href="%s" class="c-wiki-link">%s</a>',
                htmlspecialchars($this->urlGenerator->generate('members_profile', ['username' => $parts[1]])),
                htmlspecialchars($parts[2] ?? $parts[1], \ENT_NOQUOTES),
            ));
        }

        return $this->protect('<span class="c-wiki-template">' . htmlspecialchars(preg_replace('~^subst:~i', '', $parts[0]), \ENT_NOQUOTES) . '</span>');
    }

    private function protect(string $html): string
    {
        $key = "\x01" . \count($this->protected) . "\x02";
        $this->protected[$key] = $html;

        return $key;
    }

    /** puts the protected pieces back, also pieces within pieces */
    private function restore(string $html): string
    {
        for ($i = 0; $i < 5 && str_contains($html, "\x01"); ++$i) {
            $html = strtr($html, $this->protected);
        }

        return $html;
    }
}
