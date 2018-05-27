<?php

namespace AppBundle\Doctrine;

class InFolderType extends EnumType
{
    const NORMAL = 'Normal';
    const JUNK = 'junk';
    const SPAM = 'Spam';
    const DRAFT = 'Draft';
    const REQUESTS = 'requests';

    protected $name = 'in_folder';
    protected $values = [
        self::NORMAL,
        self::JUNK,
        self::SPAM,
        self::DRAFT,
        self::REQUESTS
    ];
}
