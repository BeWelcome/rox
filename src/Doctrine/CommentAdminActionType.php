<?php

namespace App\Doctrine;

use Override;

class CommentAdminActionType extends EnumType
{
    public const NOTHING_NEEDED = 'NothingNeeded';
    public const string ADMIN_CHECK = 'AdminCommentMustCheck';
    public const string SAFETY_TEAM_CHECK = 'AdminAbuserMustCheck';
    public const string ADMIN_CHECKED = 'Checked';

    #[Override]
    protected string $name = 'comment_admin_action';

    #[Override]
    protected array $values = [
        self::NOTHING_NEEDED,
        self::ADMIN_CHECK,
        self::SAFETY_TEAM_CHECK,
        self::ADMIN_CHECKED,
    ];
}
