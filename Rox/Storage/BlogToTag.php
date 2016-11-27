<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogToTag
 *
 * @ORM\Table(name="blog_to_tag", indexes={@ORM\Index(name="blog_tag_id_foreign", columns={"blog_tag_id_foreign"}), @ORM\Index(name="blog_id_foreign", columns={"blog_id_foreign"})})
 * @ORM\Entity
 */
class BlogToTag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="blog_id_foreign", type="integer", nullable=false)
     */
    private $blogIdForeign = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_tag_id_foreign", type="integer", nullable=false)
     */
    private $blogTagIdForeign = '0';

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
     * @return BlogToTag
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
     * Set blogTagIdForeign
     *
     * @param integer $blogTagIdForeign
     *
     * @return BlogToTag
     */
    public function setBlogTagIdForeign($blogTagIdForeign)
    {
        $this->blogTagIdForeign = $blogTagIdForeign;

        return $this;
    }

    /**
     * Get blogTagIdForeign
     *
     * @return integer
     */
    public function getBlogTagIdForeign()
    {
        return $this->blogTagIdForeign;
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
