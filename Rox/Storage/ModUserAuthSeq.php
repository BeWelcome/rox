<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModUserAuthSeq
 *
 * @ORM\Table(name="mod_user_auth_seq")
 * @ORM\Entity
 */
class ModUserAuthSeq
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
