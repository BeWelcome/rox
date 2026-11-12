<?php

namespace App\Model\About;

use App\Entity\Member;
use App\Entity\TeamCoordinator;
use App\Repository\MemberRepository;
use App\Repository\TeamCoordinatorRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The coordinators of the teams of Get active, as the admins set them (Admin\VolunteerToolController).
 */
readonly class TeamCoordinators
{
    public function __construct(
        private TeamCoordinatorRepository $coordinators,
        private MemberRepository $members,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<string, string> the username of each team's coordinator, empty for none
     */
    public function usernames(): array
    {
        $coordinators = $this->coordinators->byTeam();

        return array_map(
            static fn (string $team): string => isset($coordinators[$team]) ? $coordinators[$team]->getMember()->getUsername() : '',
            array_combine(array_keys(VolunteerTeams::TEAMS), array_keys(VolunteerTeams::TEAMS)),
        );
    }

    /**
     * Sets the coordinators: a username per team, empty for none. Nothing is saved when a username doesn't exist.
     *
     * @param array<string, ?string> $usernames by team
     *
     * @return list<string> the teams with a username that doesn't exist
     */
    public function save(array $usernames): array
    {
        $members = [];
        $unknown = [];
        foreach (array_keys(VolunteerTeams::TEAMS) as $team) {
            $username = trim((string) ($usernames[$team] ?? ''));
            if ('' === $username) {
                $members[$team] = null;
                continue;
            }
            $member = $this->members->findOneBy(['username' => $username]);
            if (!$member instanceof Member) {
                $unknown[] = $team;
                continue;
            }
            $members[$team] = $member;
        }
        if ([] !== $unknown) {
            return $unknown;
        }

        $coordinators = $this->coordinators->byTeam();
        foreach ($members as $team => $member) {
            $coordinator = $coordinators[$team] ?? null;
            if (null === $member) {
                if (null !== $coordinator) {
                    $this->entityManager->remove($coordinator);
                }
            } elseif (null === $coordinator) {
                $this->entityManager->persist(new TeamCoordinator($team, $member));
            } else {
                $coordinator->setMember($member);
            }
        }
        $this->entityManager->flush();

        return [];
    }
}
