<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogTags
 *
 * @ORM\Table(name="blog_tags", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @ORM\Entity
 */
class BlogTags
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
     * @ORM\Column(name="blog_tag_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $blogTagId;



    /**
     * Set name
     *
     * @param string $name
     *
     * @return BlogTags
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
     * Get blogTagId
     *
     * @return integer
     */
    public function getBlogTagId()
    {
        return $this->blogTagId;
    }
}
