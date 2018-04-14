<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comment.
 *
 * @ORM\Table(name="comments", indexes={@ORM\Index(name="IdToMember", columns={"IdToMember"}), @ORM\Index(name="comments_ibfk_1", columns={"IdFromMember"})})
 * @ORM\Entity
 */
class Comment
{
    /**
     * @var string
     *
     * @ORM\Column(name="Lenght", type="string", nullable=false)
     */
    private $length;

    /**
     * @var string
     *
     * @ORM\Column(name="Quality", type="string", nullable=false)
     */
    private $quality = 'Neutral';

    /**
     * @var string
     *
     * @ORM\Column(name="TextFree", type="text", length=65535, nullable=false)
     */
    private $textfree;

    /**
     * @var string
     *
     * @ORM\Column(name="TextWhere", type="text", length=65535, nullable=false)
     */
    private $textwhere;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="AdminAction", type="string", nullable=false)
     */
    private $adminaction = 'NothingNeeded';

    /**
     * @var string
     *
     * @ORM\Column(name="DisplayableInCommentOfTheMonth", type="string", nullable=false)
     */
    private $displayableincommentofthemonth = 'Yes';

    /**
     * @var bool
     *
     * @ORM\Column(name="DisplayInPublic", type="boolean", nullable=false)
     */
    private $displayinpublic = '1';

    /**
     * @var bool
     *
     * @ORM\Column(name="AllowEdit", type="boolean", nullable=false)
     */
    private $allowedit = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdToMember", referencedColumnName="id")
     * })
     */
    private $toMember;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdFromMember", referencedColumnName="id")
     * })
     */
    private $fromMember;

    /**
     * Set length.
     *
     * @param string $length
     *
     * @return Comment
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length.
     *
     * @return string
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set quality.
     *
     * @param string $quality
     *
     * @return Comment
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality.
     *
     * @return string
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set textfree.
     *
     * @param string $textfree
     *
     * @return Comment
     */
    public function setTextfree($textfree)
    {
        $this->textfree = $textfree;

        return $this;
    }

    /**
     * Get textfree.
     *
     * @return string
     */
    public function getTextfree()
    {
        return $this->textfree;
    }

    /**
     * Set textwhere.
     *
     * @param string $textwhere
     *
     * @return Comment
     */
    public function setTextwhere($textwhere)
    {
        $this->textwhere = $textwhere;

        return $this;
    }

    /**
     * Get textwhere.
     *
     * @return string
     */
    public function getTextwhere()
    {
        return $this->textwhere;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Comment
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Comment
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set adminaction.
     *
     * @param string $adminaction
     *
     * @return Comment
     */
    public function setAdminaction($adminaction)
    {
        $this->adminaction = $adminaction;

        return $this;
    }

    /**
     * Get adminaction.
     *
     * @return string
     */
    public function getAdminaction()
    {
        return $this->adminaction;
    }

    /**
     * Set displayableincommentofthemonth.
     *
     * @param string $displayableincommentofthemonth
     *
     * @return Comment
     */
    public function setDisplayableincommentofthemonth($displayableincommentofthemonth)
    {
        $this->displayableincommentofthemonth = $displayableincommentofthemonth;

        return $this;
    }

    /**
     * Get displayableincommentofthemonth.
     *
     * @return string
     */
    public function getDisplayableincommentofthemonth()
    {
        return $this->displayableincommentofthemonth;
    }

    /**
     * Set displayinpublic.
     *
     * @param bool $displayinpublic
     *
     * @return Comment
     */
    public function setDisplayinpublic($displayinpublic)
    {
        $this->displayinpublic = $displayinpublic;

        return $this;
    }

    /**
     * Get displayinpublic.
     *
     * @return bool
     */
    public function getDisplayinpublic()
    {
        return $this->displayinpublic;
    }

    /**
     * Set allowedit.
     *
     * @param bool $allowedit
     *
     * @return Comment
     */
    public function setAllowedit($allowedit)
    {
        $this->allowedit = $allowedit;

        return $this;
    }

    /**
     * Get allowedit.
     *
     * @return bool
     */
    public function getAllowedit()
    {
        return $this->allowedit;
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
     * Set idtomember.
     *
     * @param Member $toMember
     *
     * @return Comment
     */
    public function setToMember(Member $toMember = null)
    {
        $this->toMember = $toMember;

        return $this;
    }

    /**
     * Get idtomember.
     *
     * @return Member
     */
    public function getToMember()
    {
        return $this->toMember;
    }

    /**
     * Set idfrommember.
     *
     * @param Member $fromMember
     *
     * @return Comment
     */
    public function setFromMember(Member $fromMember = null)
    {
        $this->fromMember = $fromMember;

        return $this;
    }

    /**
     * Get idfrommember.
     *
     * @return Member
     */
    public function getFromMember()
    {
        return $this->fromMember;
    }
}
