<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gallery
 *
 * @ORM\Table(name="gallery", indexes={@ORM\Index(name="user_id_foreign", columns={"user_id_foreign"})})
 * @ORM\Entity
 */
class Gallery
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id_foreign", type="integer", nullable=false)
     */
    private $userIdForeign;

    /**
     * @var string
     *
     * @ORM\Column(name="flags", type="blob", length=65535, nullable=false)
     */
    private $flags;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=16777215, nullable=false)
     */
    private $text;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set userIdForeign
     *
     * @param integer $userIdForeign
     *
     * @return Gallery
     */
    public function setUserIdForeign($userIdForeign)
    {
        $this->userIdForeign = $userIdForeign;

        return $this;
    }

    /**
     * Get userIdForeign
     *
     * @return integer
     */
    public function getUserIdForeign()
    {
        return $this->userIdForeign;
    }

    /**
     * Set flags
     *
     * @param string $flags
     *
     * @return Gallery
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get flags
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Gallery
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Gallery
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

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
