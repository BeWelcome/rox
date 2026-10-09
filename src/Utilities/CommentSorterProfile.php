<?php

namespace App\Utilities;

use DateTime;

/**
 * CommentSorter for the legacy profile page, whose comments are database rows
 * with created and updated as date strings.
 */
class CommentSorterProfile extends CommentSorter
{
    protected function getCreated($comment)
    {
        return new DateTime($comment->created);
    }

    protected function getUpdated($comment)
    {
        return $comment->updated ? new DateTime($comment->updated) : null;
    }
}
