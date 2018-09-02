<?php

namespace AppBundle\Doctrine;

class CommentAdminActionType extends EnumType
{
    const NOTHING_NEEDED = 'NothingNeeded';
    const ADMIN_CHECK = 'AdminCommentMustCheck';
    const SAFETY_TEAM_CHECK = 'AdminAbuserMustCheck';
    const ADMIN_CHECKED = 'Checked';

    protected $name = 'comment_admin_action';
    protected $values = [
        self::NOTHING_NEEDED,
        self::ADMIN_CHECK,
        self::SAFETY_TEAM_CHECK,
        self::ADMIN_CHECKED,
    ];
}
