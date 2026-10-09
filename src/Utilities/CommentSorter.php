<?php

namespace App\Utilities;

use DateTimeImmutable;

/**
 * Sorts comment pairs after lowest created as long as updated is null.
 *
 * In case updated has been set (new experience) then the highest updated wins.
 *
 * Works on Comment entities; CommentSorterProfile reads the legacy profile rows.
 */
class CommentSorter
{
    private DateTimeImmutable $early20thCentury;
    private DateTimeImmutable $farFuture;

    public function __construct()
    {
        $this->early20thCentury = new DateTimeImmutable('01-01-1900');
        $this->farFuture = new DateTimeImmutable('01-01-3000');
    }

    public function sortComments(array $comments): array
    {
        usort($comments, [$this, 'commentsCompare']);

        return $comments;
    }

    protected function getCreated($comment)
    {
        return $comment->getCreated();
    }

    protected function getUpdated($comment)
    {
        return $comment->getUpdated();
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

    private function getCreatedCriterion($comment)
    {
        $createdTo = isset($comment['to']) ? $this->getCreated($comment['to']) : $this->farFuture;
        $createdFrom = isset($comment['from']) ? $this->getCreated($comment['from']) : $this->farFuture;

        return min($createdTo, $createdFrom);
    }

    private function getUpdatedCriterion($comment)
    {
        $updatedTo = isset($comment['to']) ? ($this->getUpdated($comment['to']) ?? $this->early20thCentury) : $this->early20thCentury;
        $updatedFrom = isset($comment['from']) ? ($this->getUpdated($comment['from']) ?? $this->early20thCentury) : $this->early20thCentury;

        return max($updatedTo, $updatedFrom);
    }
}
