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
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Comment.
 *
 * @ORM\Table(name="comments")
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Comment
{
    /**
     * @ORM\Column(name="relations", type="comment_relations", nullable=false)
     */
    private string $relations;

    /**
     * @ORM\Column(name="Quality", type="comment_quality", nullable=false)
     */
    private string $quality = CommentQualityType::NEUTRAL;

    /**
     * @ORM\Column(name="TextFree", type="text", length=65535, nullable=false)
     */
    private string $textfree;

    /**
     * @ORM\Column(name="TextWhere", type="text", length=65535, nullable=false)
     */
    private string $textwhere = '';

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private ?DateTime $updated;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="AdminAction", type="comment_admin_action", nullable=false)
     */
    private string $adminAction = CommentAdminActionType::NOTHING_NEEDED;

    /**
     * @ORM\Column(name="DisplayableInCommentOfTheMonth", type="string", nullable=false)
     */
    private string $allowDisplayInCommentOfTheMonth = 'Yes';

    /**
     * @ORM\Column(name="DisplayInPublic", type="boolean", nullable=false)
     */
    private bool $displayInPublic = true;

    /**
     * @ORM\Column(name="AllowEdit", type="boolean", nullable=false)
     */
    private bool $editingAllowed = true;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdToMember", referencedColumnName="id")
     * })
     */
    private Member $toMember;

    /**
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdFromMember", referencedColumnName="id")
     * })
     */
    private Member $fromMember;

    public function setRelations(string $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    public function getRelations(): string
    {
        return $this->relations;
    }

    public function setQuality(string $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getQuality(): string
    {
        return $this->quality;
    }

    public function setTextfree(string $textfree): self
    {
        $this->textfree = $textfree;

        return $this;
    }

    public function getTextfree(): string
    {
        return $this->textfree;
    }

    public function setTextwhere($textwhere): self
    {
        $this->textwhere = $textwhere;

        return $this;
    }

    public function getTextwhere(): string
    {
        return $this->textwhere;
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?Carbon
    {
        if (null === $this->updated) {
            return null;
        }

        return Carbon::instance($this->updated);
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setAdminAction(string $adminAction): self
    {
        $this->adminAction = $adminAction;

        return $this;
    }

    public function getAdminAction(): string
    {
        return $this->adminAction;
    }

    public function setDisplayInPublic(bool $displayInPublic): self
    {
        $this->displayInPublic = $displayInPublic;

        return $this;
    }

    public function getDisplayInPublic(): bool
    {
        return $this->displayInPublic;
    }

    public function setEditingAllowed(bool $editingAllowed): self
    {
        $this->editingAllowed = $editingAllowed;

        return $this;
    }

    public function getEditingAllowed(): bool
    {
        return $this->editingAllowed;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setToMember(Member $toMember = null): self
    {
        $this->toMember = $toMember;

        return $this;
    }

    public function getToMember(): Member
    {
        return $this->toMember;
    }

    public function setFromMember(Member $fromMember = null): self
    {
        $this->fromMember = $fromMember;

        return $this;
    }

    public function getFromMember(): Member
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
        if ($this->fromMember === $loggedInMember) {
            return 3;
        }

        // do not show comment
        return 0;
    }

    public function getEditCondition(Member $loggedInMember): bool
    {
        // don't allow edit is not logged in as writer
        if ($this->fromMember !== $loggedInMember) {
            return false;
        }

        // return state of comment in other cases (negative comments are locked by default).
        return $this->getEditingAllowed();
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = null;
    }
}
