<?php

namespace AppBundle\Model;


use Doctrine\Bundle\DoctrineBundle\Registry;

class BaseModel
{
    /** @var Registry */
    protected $em;

    public function __construct(Registry $em)
    {
        $this->em = $em;
    }
}