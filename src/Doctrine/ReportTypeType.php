<?php

namespace App\Doctrine;

class ReportTypeType extends EnumType
{
    public const string SEE_TEXT = 'SeeText';
    public const string ALLOW_ME_TO_EDIT = 'AllowMeToEdit';
    public const string INSULTING = 'Insults';
    public const string REMOVE_MY_POST = 'RemoveMyPost';

    protected string $name = 'report_type';

    protected array $values = [
        self::SEE_TEXT,
        self::ALLOW_ME_TO_EDIT,
        self::INSULTING,
        self::REMOVE_MY_POST,
    ];
}
