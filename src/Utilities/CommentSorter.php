<?php

namespace App\Utilities;

use Carbon\CarbonImmutable;

/**
 * Sorts comment pairs after lowest created as long as updated is null.
 *
 * In case updated has been set (new experience) then the highest updated wins.
 */
class CommentSorter
{
    private readonly CarbonImmutable $early20thCentury;
    private readonly CarbonImmutable $farFuture;

    public function __construct()
    {
        $this->early20thCentury = new CarbonImmutable('01-01-1900');
        $this->farFuture = new CarbonImmutable('01-01-3000');
    }

    public function sortComments(array $comments): array
    {
        usort($comments, [$this, 'commentsCompare']);

        return $comments;
    }

    /**
     * PHPMD doesn't see the usort call above.
     *
     * @SuppressWarnings("PHPMD.UnusedPrivateMethod")
     */
    private function commentsCompare(array $a, array $b): int
    {
        $aCriterionDate = max($this->getCreatedCriterion($a), $this->getUpdatedCriterion($a));
        $bCriterionDate = max($this->getCreatedCriterion($b), $this->getUpdatedCriterion($b));

        return (-1) * ($aCriterionDate <=> $bCriterionDate);
    }

    private function getCreatedCriterion(array $comment): CarbonImmutable
    {
        $createdTo = isset($comment['to'])
            ? new CarbonImmutable($comment['to']->getCreated()) : $this->farFuture;
        $createdFrom = isset($comment['from'])
            ? new CarbonImmutable($comment['from']->getCreated()) : $this->farFuture;

        return min($createdTo, $createdFrom);
    }

    private function getUpdatedCriterion(array $comment): CarbonImmutable
    {
        $updatedTo = isset($comment['to'])
            ? ($comment['to']->getUpdated() ?? $this->early20thCentury) : $this->early20thCentury;
        $updatedFrom = isset($comment['from'])
            ? ($comment['from']->getUpdated() ?? $this->early20thCentury) : $this->early20thCentury;

        return max(new CarbonImmutable($updatedTo), new CarbonImmutable($updatedFrom));
    }
}
