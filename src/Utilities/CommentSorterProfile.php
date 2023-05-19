<?php

namespace App\Utilities;

use DateTime;
use DateTimeImmutable;

/**
 * Sorts comment pairs after lowest created as long as updated is null.
 *
 * In case updated has been set (new experience) then the highest updated wins.
 */
class CommentSorterProfile
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

    private function commentsCompare($a, $b)
    {
        $aCriterionDate = max($this->getCreatedCriterion($a), $this->getUpdatedCriterion($a));
        $bCriterionDate = max($this->getCreatedCriterion($b), $this->getUpdatedCriterion($b));

        return (-1) * ($aCriterionDate <=> $bCriterionDate);
    }

    private function getCreatedCriterion($comment)
    {
        $createdTo = isset($comment['to']) ? new DateTime($comment['to']->created) : $this->farFuture;
        $createdFrom = isset($comment['from']) ? new DateTime($comment['from']->created) : $this->farFuture;

        return min($createdTo, $createdFrom);
    }

    private function getUpdatedCriterion($comment)
    {
        $updatedTo = isset($comment['to']) ? ($comment['to']->updated ? new DateTime($comment['to']->updated) : $this->early20thCentury) : $this->early20thCentury;
        $updatedFrom = isset($comment['from']) ? ($comment['from']->updated ? new DateTime($comment['from']->updated) : $this->early20thCentury) : $this->early20thCentury;

        return max($updatedTo, $updatedFrom);
    }
}
