<?php

namespace AppBundle\Doctrine;

class InFolderType extends SetType
{
    protected $name = 'in_folder';
    protected $values = ['normal', 'junk', 'spam', 'draft', 'requests'];
}
