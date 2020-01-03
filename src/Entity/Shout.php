<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="shouts")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 */
class Shout
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="member_id_foreign", referencedColumnName="id")
     * })
     */
    private $member;

    /**
     * @ORM\Column(name="table", type="string", length=75)
     */
    private $table;

    /**
     * @ORM\Column(name="table_id", type="integer", length=75)
     */
    private $table_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $title;

    /**
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable($table): self
    {
        $this->table = $table;
        return $this;
    }

    public function getTableId(): int
    {
        return $this->table_id;
    }

    public function setTableId(int $table_id): self
    {
        $this->table_id = $table_id;
        return $this;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }
}
