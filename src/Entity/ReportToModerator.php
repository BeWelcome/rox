<?php

namespace App\Entity;

use App\Doctrine\ReportStatusType;
use App\Doctrine\ReportTypeType;
use App\Doctrine\WhoSpokeLastType;
use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReportToModerator.
 *
 * @ORM\Table(name="reports_to_moderators", indexes={@ORM\Index(name="IdReporter", columns={"IdReporter", "IdPost", "IdThread"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 */
class ReportToModerator
{
    use LifecycleCallbacksTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="PostComment", type="text", length=65535, nullable=false)
     */
    private $postComment;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member")
     * @ORM\JoinColumn(name="IdReporter", referencedColumnName="id")
     */
    private $reporter;

    /**
     * @var string
     *
     * @ORM\Column(name="ModeratorComment", type="text", length=65535, nullable=false)
     */
    private $moderatorComment;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member")
     * @ORM\JoinColumn(name="IdModerator", referencedColumnName="id")
     */
    private $moderator;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="report_status", nullable=false)
     */
    private $status = ReportStatusType::OPEN;

    /**
     * @var ForumPost
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ForumPost")
     * @ORM\Column(name="IdPost", type="integer", nullable=false)
     */
    private $post;

    /**
     * @var ForumThread
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ForumThread")
     * @ORM\Column(name="IdThread", type="integer", nullable=false)
     */
    private $thread;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="report_type", nullable=false)
     */
    private $type = ReportTypeType::SEE_TEXT;

    /**
     * @var string
     *
     * @ORM\Column(name="LastWhoSpoke", type="who_spoke_last", nullable=false)
     */
    private $lastWhoSpoke = WhoSpokeLastType::MEMBER;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set postcomment.
     *
     * @param string $postComment
     *
     * @return ReportToModerator
     */
    public function setPostComment($postComment)
    {
        $this->postComment = $postComment;

        return $this;
    }

    /**
     * Get postcomment.
     *
     * @return string
     */
    public function getPostComment()
    {
        return $this->postComment;
    }

    /**
     * Set moderatorcomment.
     *
     * @param string $moderatorComment
     *
     * @return ReportToModerator
     */
    public function setModeratorComment($moderatorComment)
    {
        $this->moderatorComment = $moderatorComment;

        return $this;
    }

    /**
     * Get moderatorcomment.
     *
     * @return string
     */
    public function getModeratorComment()
    {
        return $this->moderatorComment;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return ReportToModerator
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ReportToModerator
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
     * Set last who spoke.
     *
     * @param string $lastWhoSpoke
     *
     * @return ReportToModerator
     */
    public function setLastWhoSpoke($lastWhoSpoke)
    {
        $this->lastWhoSpoke = $lastWhoSpoke;

        return $this;
    }

    /**
     * Get last who spoke.
     *
     * @return string
     */
    public function getLastWhoSpoke()
    {
        return $this->lastWhoSpoke;
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

    public function getReporter(): Member
    {
        return $this->reporter;
    }

    public function setReporter(Member $reporter): void
    {
        $this->reporter = $reporter;
    }

    public function getModerator(): Member
    {
        return $this->moderator;
    }

    public function setModerator(Member $moderator): void
    {
        $this->moderator = $moderator;
    }

    /**
     * @return ForumPost
     */
    public function getPost(): int
    {
        return $this->post;
    }

    public function setPost(ForumPost $post): void
    {
        $this->post = $post;
    }

    /**
     * @return ForumThread
     */
    public function getThread(): int
    {
        return $this->thread;
    }

    public function setThread(ForumThread $thread): void
    {
        $this->thread = $thread;
    }
}
