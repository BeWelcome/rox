<?php

namespace App\Doctrine;

class ReportTypeType extends EnumType
{
    public const SEE_TEXT = 'SeeText';
    public const ALLOW_ME_TO_EDIT = 'AllowMeToEdit';
    public const INSULTING = 'Insults';
    public const REMOVE_MY_POST = 'RemoveMyPost';

    /** @var string */
    protected $name = 'report_type';

    /** @var array */
    protected $values = [
        self::SEE_TEXT,
        self::ALLOW_ME_TO_EDIT,
        self::INSULTING,
        self::REMOVE_MY_POST,
    ];
}
