<?php

namespace App\Service;

use Twig\Attribute\AsTwigFunction;

/**
 * What changed between two versions of a text, word by word, for translators (the English text before and after its
 * last change). HTML tags count as words of their own: translators write the markup too.
 */
final class TextDiff
{
    public const string SAME = 'same';
    public const string REMOVED = 'removed';
    public const string ADDED = 'added';

    /** Beyond this many word pairs the texts are shown as removed and added as a whole (the diff needs a table of them) */
    private const int MAX_CELLS = 250000;

    /**
     * The parts of the new text, in order: unchanged, removed (only in the old one) and added (only in the new one).
     * Neighbouring parts of the same kind are joined.
     *
     * @return list<array{type: string, text: string}>
     */
    #[AsTwigFunction('text_diff')]
    public function diff(string $old, string $new): array
    {
        [$beforeKeys, $beforeTexts] = $this->tokens($old);
        [$afterKeys, $afterTexts] = $this->tokens($new);
        $beforeCount = \count($beforeKeys);
        $afterCount = \count($afterKeys);

        // the same start and end, then the diff of what is between
        $start = 0;
        while ($start < $beforeCount && $start < $afterCount && $beforeKeys[$start] === $afterKeys[$start]) {
            ++$start;
        }
        $end = 0;
        while (
            $end < $beforeCount - $start && $end < $afterCount - $start
            && $beforeKeys[$beforeCount - 1 - $end] === $afterKeys[$afterCount - 1 - $end]
        ) {
            ++$end;
        }

        $parts = [];
        // unchanged words as the new text has them (with its spaces)
        $this->add($parts, self::SAME, \array_slice($afterTexts, 0, $start));
        $before = \array_slice($beforeKeys, $start, $beforeCount - $start - $end);
        $after = \array_slice($afterKeys, $start, $afterCount - $start - $end);
        if (\count($before) * \count($after) > self::MAX_CELLS) {
            $this->add($parts, self::REMOVED, \array_slice($beforeTexts, $start, \count($before)));
            $this->add($parts, self::ADDED, \array_slice($afterTexts, $start, \count($after)));
        } else {
            foreach ($this->middle($before, $after) as [$type, $i, $j]) {
                $this->add($parts, $type, [self::REMOVED === $type ? $beforeTexts[$start + $i] : $afterTexts[$start + $j]]);
            }
        }
        $this->add($parts, self::SAME, \array_slice($afterTexts, $afterCount - $end));

        return $parts;
    }

    /**
     * Tags, words and punctuation, compared without the spaces that follow them (keys), shown with them (texts).
     *
     * @return array{list<string>, list<string>}
     */
    private function tokens(string $text): array
    {
        preg_match_all('~(<[^>]*>|[\p{L}\p{N}_]+|[^\s<\p{L}\p{N}_]+)(\s*)~u', $text, $matches);
        $keys = $matches[1];
        $texts = array_map(static fn (string $token, string $space): string => $token . $space, $matches[1], $matches[2]);
        // spaces before the first word go with it
        if ([] !== $texts && 1 === preg_match('~^\s+~u', $text, $leading)) {
            $texts[0] = $leading[0] . $texts[0];
        }

        return [$keys, $texts];
    }

    /**
     * The longest common subsequence of the tokens, as a list of steps: the kind and the positions in before and
     * after.
     *
     * @param list<string> $before
     * @param list<string> $after
     *
     * @return list<array{string, int, int}>
     */
    private function middle(array $before, array $after): array
    {
        $rows = \count($before);
        $columns = \count($after);
        // lengths[i][j]: the common part of before from i and after from j
        $lengths = array_fill(0, $rows + 1, array_fill(0, $columns + 1, 0));
        for ($i = $rows - 1; $i >= 0; --$i) {
            for ($j = $columns - 1; $j >= 0; --$j) {
                $lengths[$i][$j] = $before[$i] === $after[$j]
                    ? $lengths[$i + 1][$j + 1] + 1
                    : max($lengths[$i + 1][$j], $lengths[$i][$j + 1]);
            }
        }

        $steps = [];
        $i = 0;
        $j = 0;
        while ($i < $rows && $j < $columns) {
            if ($before[$i] === $after[$j]) {
                $steps[] = [self::SAME, $i++, $j++];
            } elseif ($lengths[$i + 1][$j] >= $lengths[$i][$j + 1]) {
                $steps[] = [self::REMOVED, $i++, $j];
            } else {
                $steps[] = [self::ADDED, $i, $j++];
            }
        }
        for (; $i < $rows; ++$i) {
            $steps[] = [self::REMOVED, $i, $j];
        }
        for (; $j < $columns; ++$j) {
            $steps[] = [self::ADDED, $i, $j];
        }

        return $steps;
    }

    /**
     * @param list<array{type: string, text: string}> $parts
     * @param list<string>                            $tokens
     */
    private function add(array &$parts, string $type, array $tokens): void
    {
        if ([] === $tokens) {
            return;
        }
        $text = implode('', $tokens);
        $last = \count($parts) - 1;
        if ($last >= 0 && $parts[$last]['type'] === $type) {
            $parts[$last]['text'] .= $text;
        } else {
            $parts[] = ['type' => $type, 'text' => $text];
        }
    }
}
