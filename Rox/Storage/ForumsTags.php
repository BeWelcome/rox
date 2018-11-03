<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForumsTags
 *
 * @ORM\Table(name="forums_tags", indexes={@ORM\Index(name="id", columns={"id"}), @ORM\Index(name="tag", columns={"tag"})})
 * @ORM\Entity
 */
class ForumsTags
{
    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=64, nullable=false)
     */
    private $tag;

    /**
     * @var string
     *
     * @ORM\Column(name="tag_description", type="string", length=255, nullable=true)
     */
    private $tagDescription;

    /**
     * @var boolean
     *
     * @ORM\Column(name="tag_position", type="boolean", nullable=false)
     */
    private $tagPosition = '250';

    /**
     * @var integer
     *
     * @ORM\Column(name="counter", type="integer", nullable=false)
     */
    private $counter = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdName", type="integer", nullable=false)
     */
    private $idname = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdDescription", type="integer", nullable=false)
     */
    private $iddescription = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'Member';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="tagid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $tagid;



    /**
     * Set tag
     *
     * @param string $tag
     *
     * @return ForumsTags
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set tagDescription
     *
     * @param string $tagDescription
     *
     * @return ForumsTags
     */
    public function setTagDescription($tagDescription)
    {
        $this->tagDescription = $tagDescription;

        return $this;
    }

    /**
     * Get tagDescription
     *
     * @return string
     */
    public function getTagDescription()
    {
        return $this->tagDescription;
    }

    /**
     * Set tagPosition
     *
     * @param boolean $tagPosition
     *
     * @return ForumsTags
     */
    public function setTagPosition($tagPosition)
    {
        $this->tagPosition = $tagPosition;

        return $this;
    }

    /**
     * Get tagPosition
     *
     * @return boolean
     */
    public function getTagPosition()
    {
        return $this->tagPosition;
    }

    /**
     * Set counter
     *
     * @param integer $counter
     *
     * @return ForumsTags
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return integer
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Set idname
     *
     * @param integer $idname
     *
     * @return ForumsTags
     */
    public function setIdname($idname)
    {
        $this->idname = $idname;

        return $this;
    }

    /**
     * Get idname
     *
     * @return integer
     */
    public function getIdname()
    {
        return $this->idname;
    }

    /**
     * Set iddescription
     *
     * @param integer $iddescription
     *
     * @return ForumsTags
     */
    public function setIddescription($iddescription)
    {
        $this->iddescription = $iddescription;

        return $this;
    }

    /**
     * Get iddescription
     *
     * @return integer
     */
    public function getIddescription()
    {
        return $this->iddescription;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ForumsTags
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return ForumsTags
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    /**
     * Get tagid
     *
     * @return integer
     */
    public function getTagid()
    {
        return $this->tagid;
    }
}
