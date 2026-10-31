<?php

namespace App\Doctrine;

use Override;

class PostCanStillEditType extends EnumType
{
    public const string CAN_STILL_EDIT = 'Yes';
    public const string EDIT_DISABLED = 'No';

    #[Override]
    protected string $name = 'can_still_edit';

    #[Override]
    protected array $values = [
        self::CAN_STILL_EDIT,
        self::EDIT_DISABLED,
    ];
}
