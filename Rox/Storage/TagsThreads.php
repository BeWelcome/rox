<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TagsThreads
 *
 * @ORM\Table(name="tags_threads", indexes={@ORM\Index(name="IdTag", columns={"IdTag"}), @ORM\Index(name="IdThread", columns={"IdThread"})})
 * @ORM\Entity
 */
class TagsThreads
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdTag", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idtag;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdThread", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idthread;



    /**
     * Set idtag
     *
     * @param integer $idtag
     *
     * @return TagsThreads
     */
    public function setIdtag($idtag)
    {
        $this->idtag = $idtag;

        return $this;
    }

    /**
     * Get idtag
     *
     * @return integer
     */
    public function getIdtag()
    {
        return $this->idtag;
    }

    /**
     * Set idthread
     *
     * @param integer $idthread
     *
     * @return TagsThreads
     */
    public function setIdthread($idthread)
    {
        $this->idthread = $idthread;

        return $this;
    }

    /**
     * Get idthread
     *
     * @return integer
     */
    public function getIdthread()
    {
        return $this->idthread;
    }
}
