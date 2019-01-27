<?php

namespace App\Doctrine;

class CommentAdminActionType extends EnumType
{
    const NOTHING_NEEDED = 'NothingNeeded';
    const ADMIN_CHECK = 'AdminCommentMustCheck';
    const SAFETY_TEAM_CHECK = 'AdminAbuserMustCheck';
    const ADMIN_CHECKED = 'Checked';

    /** @var string */
    protected $name = 'comment_admin_action';

    /** @var array */
    protected $values = [
        self::NOTHING_NEEDED,
        self::ADMIN_CHECK,
        self::SAFETY_TEAM_CHECK,
        self::ADMIN_CHECKED,
    ];
}
