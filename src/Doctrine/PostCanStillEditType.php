<?php

namespace App\Doctrine;

class PostCanStillEditType extends EnumType
{
    public const CAN_STILL_EDIT = 'Yes';
    public const EDIT_DISABLED = 'No';

    /** @var string */
    protected $name = 'can_still_edit';

    /** @var array */
    protected $values = [
        self::CAN_STILL_EDIT,
        self::EDIT_DISABLED,
    ];
}
