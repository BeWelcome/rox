<?php

namespace AppBundle\Doctrine;

class InFolderType extends SetType
{
    protected $name = 'infolder';
    protected $values = ['normal', 'junk', 'spam', 'draft', 'request'];
}
