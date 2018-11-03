<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogTagsSeq
 *
 * @ORM\Table(name="blog_tags_seq")
 * @ORM\Entity
 */
class BlogTagsSeq
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
