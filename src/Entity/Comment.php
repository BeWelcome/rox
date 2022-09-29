<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Comment.
 *
 * @ORM\Table(name="comments", indexes={@ORM\Index(name="IdToMember", columns={"IdToMember"}), @ORM\Index(name="comments_ibfk_1", columns={"IdFromMember"})})
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Comment
{
    /**
     * @var string
     *
     * @ORM\Column(name="relations", type="comment_relations", nullable=false)
     */
    private $relations;

    /**
     * @var string
     *
     * @ORM\Column(name="Quality", type="comment_quality", nullable=false)
     */
    private $quality = CommentQualityType::NEUTRAL;

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
     * @ORM\Column(name="AdminAction", type="comment_admin_action", nullable=false)
     */
    private $adminAction = CommentAdminActionType::NOTHING_NEEDED;

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
    private $displayInPublic = '1';

    /**
     * @var bool
     *
     * @ORM\Column(name="AllowEdit", type="boolean", nullable=false)
     */
    private $allowedit = '1';

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
     * Set relations.
     *
     * @param string $relations
     *
     * @return Comment
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Get relations.
     *
     * @return string
     */
    public function getRelations()
    {
        return $this->relations;
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
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
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
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set adminaction.
     *
     * @param string $adminAction
     *
     * @return Comment
     */
    public function setAdminAction($adminAction)
    {
        $this->adminAction = $adminAction;

        return $this;
    }

    /**
     * Get adminaction.
     *
     * @return string
     */
    public function getAdminAction()
    {
        return $this->adminAction;
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
     * @param bool $displayInPublic
     *
     * @return Comment
     */
    public function setDisplayInPublic($displayInPublic)
    {
        $this->displayInPublic = $displayInPublic;

        return $this;
    }

    /**
     * Get displayinpublic.
     *
     * @return bool
     */
    public function getDisplayInPublic()
    {
        return $this->displayInPublic;
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

    public function getShowCondition(Member $loggedInMember): int
    {
        // show comment when marked as display in public (default situation)
        if ($this->displayInPublic) {
            return 1;
        }
        // show comment to Safety team
        if (in_array(Member::ROLE_ADMIN_COMMENTS, $loggedInMember->getRoles())) {
            return 2;
        }
        // show comment to writer
        if ($this->fromMember == $loggedInMember) return 3;
        // do not show comment

        return 0;
    }

    function getEditCondition(Member $loggedInMember){

        // don't allow edit bad comment if not marked so
        if ($this->quality == 'Bad' && $this->allowedit != 1) return false;
        // don't allow edit is not logged in as writer
        if ($this->fromMember != $loggedInMember) return false;

        // allow edit
        return true;
    }

}
