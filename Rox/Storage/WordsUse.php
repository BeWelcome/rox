<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WordsUse
 *
 * @ORM\Table(name="words_use")
 * @ORM\Entity
 */
class WordsUse
{
    /**
     * @var integer
     *
     * @ORM\Column(name="NbUse", type="integer", nullable=false)
     */
    private $nbuse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=256)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $code;



    /**
     * Set nbuse
     *
     * @param integer $nbuse
     *
     * @return WordsUse
     */
    public function setNbuse($nbuse)
    {
        $this->nbuse = $nbuse;

        return $this;
    }

    /**
     * Get nbuse
     *
     * @return integer
     */
    public function getNbuse()
    {
        return $this->nbuse;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
