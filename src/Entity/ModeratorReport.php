<?php

namespace App\Entity;

use App\Doctrine\ReportStatusType;
use App\Doctrine\WhoSpokeLastType;
use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * A member's report about a forum post to the forum moderators, with what the member and the moderators wrote
 * (App\Model\ForumModerationModel). In a table of its own, `moderator_report`, filled from main's
 * `reports_to_moderators` (App\Entity\ReportToModerator), which stays as main uses it: there a report always has a
 * moderator (0 for none), here it has none until a moderator answers.
 */
#[ORM\Table(name: 'moderator_report')]
#[ORM\Index(name: 'moderator_report_post_idx', columns: ['post_id', 'reporter_id'])]
#[ORM\Index(name: 'moderator_report_thread_idx', columns: ['thread_id'])]
#[ORM\Index(name: 'moderator_report_status_idx', columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class ModeratorReport
{
    use LifecycleCallbacksTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    /** The messages of the member and the moderators, the newest first (ForumModerationModel::getMessages()). */
    #[ORM\Column(name: 'post_comment', type: 'text', length: 16777215, nullable: false)]
    private string $postComment = '';

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'reporter_id', referencedColumnName: 'id', nullable: false)]
    private Member $reporter;

    /** The moderator who answered last; none while nobody answered. */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'moderator_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $moderator = null;

    #[ORM\Column(name: 'status', type: 'report_status', nullable: false)]
    private string $status = ReportStatusType::OPEN;

    /** The reported post's id (no relation: the report stays when the post goes). */
    #[ORM\Column(name: 'post_id', type: 'integer', nullable: false)]
    private int $post = 0;

    /** The reported post's thread id. */
    #[ORM\Column(name: 'thread_id', type: 'integer', nullable: false)]
    private int $thread = 0;

    #[ORM\Column(name: 'last_who_spoke', type: 'who_spoke_last', nullable: false)]
    private string $lastWhoSpoke = WhoSpokeLastType::MEMBER;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostComment(): string
    {
        return $this->postComment;
    }

    public function setPostComment(string $postComment): void
    {
        $this->postComment = $postComment;
    }

    public function getReporter(): Member
    {
        return $this->reporter;
    }

    public function setReporter(Member $reporter): void
    {
        $this->reporter = $reporter;
    }

    public function getModerator(): ?Member
    {
        return $this->moderator;
    }

    public function setModerator(?Member $moderator): void
    {
        $this->moderator = $moderator;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPostId(): int
    {
        return $this->post;
    }

    public function setPost(ForumPost $post): void
    {
        $this->post = (int) $post->getId();
        $this->thread = (int) $post->getThread()?->getId();
    }

    public function getThreadId(): int
    {
        return $this->thread;
    }

    public function getLastWhoSpoke(): string
    {
        return $this->lastWhoSpoke;
    }

    public function setLastWhoSpoke(string $lastWhoSpoke): void
    {
        $this->lastWhoSpoke = $lastWhoSpoke;
    }
}
