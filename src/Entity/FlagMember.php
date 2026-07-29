<?php

namespace App\Entity;

use App\Repository\FlagMemberRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'flagsmembers')]
#[ORM\Index(name: 'flagsmembers_members', columns: ['IdMember', 'IdFlag'])]
#[ORM\Entity(repositoryClass: FlagMemberRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FlagMember
{
    #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\JoinColumn(name: 'IdFlag', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Flag::class)]
    private Flag $flag;

    #[ORM\Column(name: 'Level', type: 'integer', nullable: false)]
    private int $level = 0;

    #[ORM\Column(name: 'Scope', type: 'text', length: 255, nullable: false)]
    private string $scope;

    #[ORM\Column(name: 'Comment', type: 'text', length: 65535, nullable: false)]
    private string $comment;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setFlag(Flag $flag): self
    {
        $this->flag = $flag;

        return $this;
    }

    public function getFlag(): Flag
    {
        return $this->flag;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setUpdated(?DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?Carbon
    {
        return Carbon::make($this->updated);
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

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!isset($this->created)) {
            $this->created = new DateTime('now');
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('now');
    }
}
