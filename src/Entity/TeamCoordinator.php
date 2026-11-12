<?php

namespace App\Entity;

use App\Repository\TeamCoordinatorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The member to contact about a team of Get active (App\Model\About\VolunteerTeams), set by the admins.
 */
#[ORM\Table(name: 'team_coordinator')]
#[ORM\Entity(repositoryClass: TeamCoordinatorRepository::class)]
class TeamCoordinator
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING, length: 32)]
        private string $team,
        #[ORM\ManyToOne(targetEntity: Member::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Member $member,
    ) {
    }

    public function getTeam(): string
    {
        return $this->team;
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
}
