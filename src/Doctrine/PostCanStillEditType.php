<?php

namespace App\Doctrine;

class PostCanStillEditType extends EnumType
{
    public const string CAN_STILL_EDIT = 'Yes';
    public const string EDIT_DISABLED = 'No';

    protected string $name = 'can_still_edit';

    protected array $values = [
        self::CAN_STILL_EDIT,
        self::EDIT_DISABLED,
    ];
}
