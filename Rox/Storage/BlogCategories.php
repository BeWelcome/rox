<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogCategories
 *
 * @ORM\Table(name="blog_categories", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class BlogCategories
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=true)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_category_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $blogCategoryId;



    /**
     * Set name
     *
     * @param string $name
     *
     * @return BlogCategories
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return BlogCategories
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
     * Get blogCategoryId
     *
     * @return integer
     */
    public function getBlogCategoryId()
    {
        return $this->blogCategoryId;
    }
}
