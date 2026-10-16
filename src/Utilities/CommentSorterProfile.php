<?php

namespace App\Utilities;

use DateTime;
use DateTimeInterface;

/**
 * CommentSorter for the legacy profile page, whose comments are database rows
 * with created and updated as date strings (as on prod, v3.8.5).
 */
class CommentSorterProfile extends CommentSorter
{
    protected function getCreated(object $comment): DateTimeInterface
    {
        return new DateTime($comment->created);
    }

    protected function getUpdated(object $comment): ?DateTimeInterface
    {
        return $comment->updated ? new DateTime($comment->updated) : null;
    }
}
