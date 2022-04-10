<?php

namespace App\Doctrine;

class InFolderType extends EnumType
{
    public const NORMAL = 'Normal';
    public const JUNK = 'junk';
    public const SPAM = 'Spam';
    public const DRAFT = 'Draft';
    public const REQUESTS = 'requests';

    /** @var string */
    protected $name = 'in_folder';

    /** @var array */
    protected $values = [
        self::NORMAL,
        self::JUNK,
        self::SPAM,
        self::DRAFT,
        self::REQUESTS,
    ];
}
