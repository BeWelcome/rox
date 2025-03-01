<?php

namespace App\Doctrine;

class InFolderType extends EnumType
{
    public const string NORMAL = 'Normal';
    public const string JUNK = 'junk';
    public const string SPAM = 'Spam';
    public const string DRAFT = 'Draft';
    public const string REQUESTS = 'requests';

    protected string $name = 'in_folder';

    protected array $values = [
        self::NORMAL,
        self::JUNK,
        self::SPAM,
        self::DRAFT,
        self::REQUESTS,
    ];
}
