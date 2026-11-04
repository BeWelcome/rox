<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Repository\CommunityNewsCommentRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'community_news_comment')]
#[ORM\Entity(repositoryClass: CommunityNewsCommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CommunityNewsComment
{
    /**
     * @var communityNews
     *
     * A news has many comments
     */
    #[ORM\ManyToOne(targetEntity: CommunityNews::class, inversedBy: 'comments')]
    private $communityNews;

    /**
     * @var Member
     *
     * A comment has one author
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private $author;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    /**
     * @var string
     */
    #[ORM\Column(name: 'title', type: 'string', length: 75, nullable: false)]
    private $title;

    /**
     * @var string
     */
    #[ORM\Column(name: 'text', type: 'text', nullable: false)]
    private $text;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /** A reply's comment; null for a comment on the news itself. */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?CommunityNewsComment $parent = null;

    /** @var Collection<int, CommunityNewsComment> */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['created' => 'ASC', 'id' => 'ASC'])]
    private Collection $replies;

    /** When a checker removed the comment; it stays, without its text, for its replies. */
    #[ORM\Column(name: 'deleted', type: 'datetime', nullable: true)]
    private ?DateTime $deleted = null;

    /** @var Collection<int, CommunityNewsCommentReport> */
    #[ORM\OneToMany(targetEntity: CommunityNewsCommentReport::class, mappedBy: 'comment')]
    #[ORM\OrderBy(['created' => 'ASC'])]
    private Collection $reports;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
        $this->reports = new ArrayCollection();
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
     * Set title.
     *
     * @param string $title
     *
     * @return CommunityNewsComment
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text.
     *
     * @param string $text
     *
     * @return CommunityNewsComment
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
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
     * @return CommunityNews
     */
    public function getCommunityNews()
    {
        return $this->communityNews;
    }

    /**
     * @param CommunityNews $communityNews
     *
     * @return CommunityNewsComment
     */
    public function setCommunityNews($communityNews)
    {
        $this->communityNews = $communityNews;

        return $this;
    }

    public function getAuthor(): Member
    {
        return $this->author;
    }

    /**
     * @return CommunityNewsComment
     */
    public function setAuthor(Member $author)
    {
        $this->author = $author;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, CommunityNewsComment>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function getDeleted(): ?DateTime
    {
        return $this->deleted;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deleted;
    }

    /**
     * Removes the comment but keeps its place for its replies: no text, no title.
     */
    public function markDeleted(): self
    {
        $this->deleted = new DateTime();
        $this->title = '';
        $this->text = '';

        return $this;
    }

    /**
     * @return Collection<int, CommunityNewsCommentReport>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }
}
