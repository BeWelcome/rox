<?php

namespace App\Doctrine;

use Override;

class InFolderType extends EnumType
{
    public const string NORMAL = 'Normal';
    public const string JUNK = 'junk';
    public const string SPAM = 'Spam';
    public const string DRAFT = 'Draft';
    public const string REQUESTS = 'requests';

    #[Override]
    protected string $name = 'in_folder';

    #[Override]
    protected array $values = [
        self::NORMAL,
        self::JUNK,
        self::SPAM,
        self::DRAFT,
        self::REQUESTS,
    ];
}
