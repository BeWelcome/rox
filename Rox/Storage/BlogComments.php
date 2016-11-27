<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogComments
 *
 * @ORM\Table(name="blog_comments", indexes={@ORM\Index(name="id", columns={"id"}), @ORM\Index(name="blog_id_foreign", columns={"blog_id_foreign"}), @ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class BlogComments
{
    /**
     * @var integer
     *
     * @ORM\Column(name="blog_id_foreign", type="integer", nullable=false)
     */
    private $blogIdForeign = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=75, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=16777215, nullable=false)
     */
    private $text;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=true)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set blogIdForeign
     *
     * @param integer $blogIdForeign
     *
     * @return BlogComments
     */
    public function setBlogIdForeign($blogIdForeign)
    {
        $this->blogIdForeign = $blogIdForeign;

        return $this;
    }

    /**
     * Get blogIdForeign
     *
     * @return integer
     */
    public function getBlogIdForeign()
    {
        return $this->blogIdForeign;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BlogComments
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return BlogComments
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
     * @return BlogComments
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
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return BlogComments
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return integer
     */
    public function getIdmember()
    {
        return $this->idmember;
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
