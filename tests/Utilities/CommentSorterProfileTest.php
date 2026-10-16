<?php

namespace App\Tests\Utilities;

use App\Utilities\CommentSorterProfile;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The legacy profile page shows all comments, latest activity first, as on prod (v3.8.5): a new experience
 * (updated) moves its pair to the top, otherwise the pair's first comment decides.
 */
class CommentSorterProfileTest extends TestCase
{
    public function testLatestActivityFirstAndNothingLost(): void
    {
        $comments = [
            'old' => ['from' => $this->row('2012-01-01'), 'to' => $this->row('2012-02-01')],
            'recent' => ['from' => $this->row('2024-05-01')],
            'new_experience' => ['to' => $this->row('2010-01-01', '2025-01-01')],
            'middle' => ['to' => $this->row('2018-01-01')],
        ];

        $sorted = (new CommentSorterProfile())->sortComments($comments);

        $this->assertCount(4, $sorted);
        $this->assertSame(
            ['2010-01-01', '2024-05-01', '2018-01-01', '2012-01-01'],
            array_map(fn (array $pair) => ($pair['from'] ?? $pair['to'])->created, $sorted),
        );
    }

    private function row(string $created, ?string $updated = null): stdClass
    {
        $row = new stdClass();
        $row->created = $created;
        $row->updated = $updated;

        return $row;
    }
}
