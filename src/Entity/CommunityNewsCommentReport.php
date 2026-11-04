<?php

namespace App\Entity;

use App\Repository\CommunityNewsCommentReportRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A member's report of a community news comment, for the checkers (Admin\CheckerController). A member reports a
 * comment once; the checkers keep the comment (its reports go) or delete it.
 */
#[ORM\Table(name: 'community_news_comment_report')]
#[ORM\UniqueConstraint(name: 'UNIQ_CNCR_COMMENT_REPORTER', columns: ['comment_id', 'reporter_id'])]
#[ORM\Entity(repositoryClass: CommunityNewsCommentReportRepository::class)]
class CommunityNewsCommentReport
{
    public const string REASON_SPAM = 'spam';
    public const string REASON_OFFENSIVE = 'offensive';
    public const string REASON_HARASSMENT = 'harassment';
    public const string REASON_PERSONAL_DATA = 'personal_data';
    public const string REASON_OTHER = 'other';
    public const array REASONS = [
        self::REASON_SPAM,
        self::REASON_OFFENSIVE,
        self::REASON_HARASSMENT,
        self::REASON_PERSONAL_DATA,
        self::REASON_OTHER,
    ];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: CommunityNewsComment::class, inversedBy: 'reports')]
        #[ORM\JoinColumn(name: 'comment_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private CommunityNewsComment $comment,
        #[ORM\ManyToOne(targetEntity: Member::class)]
        #[ORM\JoinColumn(name: 'reporter_id', referencedColumnName: 'id', nullable: false)]
        private Member $reporter,
        #[ORM\Column(name: 'reason', type: 'string', length: 20, nullable: false)]
        private string $reason,
        #[ORM\Column(name: 'note', type: 'text', nullable: true)]
        private ?string $note = null,
    ) {
        $this->created = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getComment(): CommunityNewsComment
    {
        return $this->comment;
    }

    public function getReporter(): Member
    {
        return $this->reporter;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }
}
