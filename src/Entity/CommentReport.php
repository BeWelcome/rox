<?php

namespace App\Entity;

use App\Repository\CommentReportRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A member's report of a comment about them, as they wrote it (CommentController::reportCommentAction()). The
 * volunteers read it in the comments tool next to the comment. Deleting the comment deletes its reports.
 */
#[ORM\Table(name: 'comment_report')]
#[ORM\Entity(repositoryClass: CommentReportRepository::class)]
class CommentReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $created;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Comment::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Comment $comment,
        #[ORM\ManyToOne(targetEntity: Member::class)]
        #[ORM\JoinColumn(nullable: false)]
        private Member $reporter,
        #[ORM\Column(type: Types::TEXT)]
        private string $text,
    ) {
        $this->created = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): Comment
    {
        return $this->comment;
    }

    public function getReporter(): Member
    {
        return $this->reporter;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }
}
