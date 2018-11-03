<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripSeq
 *
 * @ORM\Table(name="trip_seq")
 * @ORM\Entity
 */
class TripSeq
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
