<?php

namespace App\Doctrine;

class InFolderType extends EnumType
{
    const NORMAL = 'Normal';
    const JUNK = 'junk';
    const SPAM = 'Spam';
    const DRAFT = 'Draft';
    const REQUESTS = 'requests';

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
