<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModUserRightsSeq
 *
 * @ORM\Table(name="mod_user_rights_seq")
 * @ORM\Entity
 */
class ModUserRightsSeq
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
