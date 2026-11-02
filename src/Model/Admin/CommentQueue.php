<?php

namespace App\Model\Admin;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Entity\Comment;

/**
 * The lists of the comments tool (templates/admin/comment/comments.html.twig): the tabs, and all comments about or by
 * one member.
 */
enum CommentQueue: string
{
    case SafetyTeam = 'safetyteam';
    case Reported = 'reported';
    case Negative = 'negative';
    case Checked = 'checked';
    case All = 'all';
    case About = 'about';
    case By = 'by';

    /**
     * Whether the comment (still) belongs in the list, after an action on it: if not, the tool shows the next one.
     */
    public function contains(Comment $comment): bool
    {
        return match ($this) {
            self::SafetyTeam => CommentAdminActionType::SAFETY_TEAM_CHECK === $comment->getAdminAction(),
            self::Reported => CommentAdminActionType::ADMIN_CHECK === $comment->getAdminAction(),
            self::Checked => CommentAdminActionType::ADMIN_CHECKED === $comment->getAdminAction(),
            self::Negative => CommentQualityType::NEGATIVE === $comment->getQuality(),
            self::All, self::About, self::By => true,
        };
    }

    /**
     * The tab that is active: the lists of one member are part of all comments.
     */
    public function tab(): self
    {
        return match ($this) {
            self::About, self::By => self::All,
            default => $this,
        };
    }
}
