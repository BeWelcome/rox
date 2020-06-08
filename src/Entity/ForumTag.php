<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tags for forum posts (no longer supported, but database table needs to exist).
 *
 * @ORM\Table(name="forums_tags", indexes={
 *     @ORM\Index(name="tag", columns={"tag"})
 * })
 * @ORM\Entity
 */
class ForumTag
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
     * @var bool
     *
     * @ORM\Column(name="tag_position", type="boolean", nullable=false)
     */
    private $tagPosition = '250';

    /**
     * @var int
     *
     * @ORM\Column(name="counter", type="integer", nullable=false)
     */
    private $counter = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="IdName", type="integer", nullable=false)
     */
    private $idname = '0';

    /**
     * @var int
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
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="tagid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $tagid;

    /**
     * Set tag.
     *
     * @param string $tag
     *
     * @return ForumTag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set tagDescription.
     *
     * @param string $tagDescription
     *
     * @return ForumTag
     */
    public function setTagDescription($tagDescription)
    {
        $this->tagDescription = $tagDescription;

        return $this;
    }

    /**
     * Get tagDescription.
     *
     * @return string
     */
    public function getTagDescription()
    {
        return $this->tagDescription;
    }

    /**
     * Set tagPosition.
     *
     * @param bool $tagPosition
     *
     * @return ForumTag
     */
    public function setTagPosition($tagPosition)
    {
        $this->tagPosition = $tagPosition;

        return $this;
    }

    /**
     * Get tagPosition.
     *
     * @return bool
     */
    public function getTagPosition()
    {
        return $this->tagPosition;
    }

    /**
     * Set counter.
     *
     * @param int $counter
     *
     * @return ForumTag
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter.
     *
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Set idname.
     *
     * @param int $idname
     *
     * @return ForumTag
     */
    public function setIdname($idname)
    {
        $this->idname = $idname;

        return $this;
    }

    /**
     * Get idname.
     *
     * @return int
     */
    public function getIdname()
    {
        return $this->idname;
    }

    /**
     * Set iddescription.
     *
     * @param int $iddescription
     *
     * @return ForumTag
     */
    public function setIddescription($iddescription)
    {
        $this->iddescription = $iddescription;

        return $this;
    }

    /**
     * Get iddescription.
     *
     * @return int
     */
    public function getIddescription()
    {
        return $this->iddescription;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ForumTag
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return ForumTag
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get tagid.
     *
     * @return int
     */
    public function getTagid()
    {
        return $this->tagid;
    }
}
