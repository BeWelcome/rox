<?php

namespace App\Wiki;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * The lines one version of a page changed from the one before. Runs of more than five unchanged lines collapse to
 * a gap with two lines of context next to the changes.
 */
final readonly class WikiDiff
{
    public const string SAME = 'same';
    public const string ADDED = 'added';
    public const string REMOVED = 'removed';
    public const string GAP = 'gap';

    private const int MAX_RUN = 5;
    private const int CONTEXT = 2;

    /**
     * @param list<array{type: string, text: string, count?: int}> $rows
     */
    private function __construct(
        public array $rows,
        public int $added,
        public int $removed,
    ) {
    }

    public static function between(string $old, string $new): self
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder());
        $lines = $differ->diffToArray(self::lines($old), self::lines($new));

        $rows = [];
        $run = [];
        $added = 0;
        $removed = 0;
        foreach ($lines as [$text, $type]) {
            if (Differ::OLD === $type) {
                $run[] = ['type' => self::SAME, 'text' => $text];
                continue;
            }
            if (Differ::ADDED !== $type && Differ::REMOVED !== $type) {
                continue;
            }
            self::flush($rows, $run, [] === $rows, false);
            $run = [];
            $rows[] = ['type' => Differ::ADDED === $type ? self::ADDED : self::REMOVED, 'text' => $text];
            Differ::ADDED === $type ? ++$added : ++$removed;
        }
        self::flush($rows, $run, [] === $rows, true);

        return new self($rows, $added, $removed);
    }

    /**
     * @param list<array{type: string, text: string, count?: int}> $rows
     * @param list<array{type: string, text: string}>              $run  unchanged lines
     */
    private static function flush(array &$rows, array $run, bool $atStart, bool $atEnd): void
    {
        if (\count($run) <= self::MAX_RUN) {
            array_push($rows, ...$run);

            return;
        }

        $before = $atStart ? [] : \array_slice($run, 0, self::CONTEXT);
        $after = $atEnd ? [] : \array_slice($run, -self::CONTEXT);
        array_push($rows, ...$before);
        $rows[] = ['type' => self::GAP, 'text' => '', 'count' => \count($run) - \count($before) - \count($after)];
        array_push($rows, ...$after);
    }

    /**
     * @return list<string>
     */
    private static function lines(string $text): array
    {
        return '' === $text ? [] : explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
    }
}
