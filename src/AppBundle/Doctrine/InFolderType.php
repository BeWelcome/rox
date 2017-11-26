<?php

namespace AppBundle\Doctrine;

class InFolderType extends EnumType
{
    protected $name = 'in_folder';
    protected $values = ['Normal', 'junk', 'Spam', 'Draft', 'requests'];
}
