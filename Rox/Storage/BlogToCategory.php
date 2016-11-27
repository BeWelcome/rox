<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogToCategory
 *
 * @ORM\Table(name="blog_to_category", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="blog_category_id_foreign", columns={"blog_category_id_foreign"}), @ORM\Index(name="blog_id_foreign", columns={"blog_id_foreign"})})
 * @ORM\Entity
 */
class BlogToCategory
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_category_id_foreign", type="integer", nullable=false)
     */
    private $blogCategoryIdForeign = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_id_foreign", type="integer", nullable=false)
     */
    private $blogIdForeign = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BlogToCategory
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
     * Set blogCategoryIdForeign
     *
     * @param integer $blogCategoryIdForeign
     *
     * @return BlogToCategory
     */
    public function setBlogCategoryIdForeign($blogCategoryIdForeign)
    {
        $this->blogCategoryIdForeign = $blogCategoryIdForeign;

        return $this;
    }

    /**
     * Get blogCategoryIdForeign
     *
     * @return integer
     */
    public function getBlogCategoryIdForeign()
    {
        return $this->blogCategoryIdForeign;
    }

    /**
     * Set blogIdForeign
     *
     * @param integer $blogIdForeign
     *
     * @return BlogToCategory
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
