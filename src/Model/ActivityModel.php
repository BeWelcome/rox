<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Dto\ActivityDto;
use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\Member;
use App\Entity\Preference;
use App\Form\ActivityRadiusType;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

class ActivityModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getLatest($page, $limit): Pagerfanta
    {
        /** @var ActivityRepository $repository */
        $repository = $this->entityManager->getRepository(Activity::class);

        return $repository->findLatest($page, $limit);
    }

    public function getProblematicActivities($page, $limit): Pagerfanta
    {
        /** @var ActivityRepository $repository */
        $repository = $this->entityManager->getRepository(Activity::class);

        return $repository->findProblematicActivities($page, $limit);
    }

    public function deleteAsSpamByChecker($activityIds): void
    {
        // delete all activities based on there ids
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->entityManager->getRepository(Activity::class);
        $activities = $activityRepository->findBy(['id' => $activityIds]);
        foreach ($activities as $activity) {
            $this->entityManager->remove($activity);
        }
        $this->entityManager->flush();
    }

    /**
     * Creates the activity with its creator as the first organizer, who attends.
     */
    public function create(ActivityDto $data, Member $creator): Activity
    {
        $activity = new Activity();
        $activity->setCreatedBy($creator);
        $activity->setStatus(Activity::STATUS_ACTIVE);
        $data->toEntity($activity);

        $organizer = new ActivityAttendee($activity, $creator, ActivityAttendee::ATTENDS_YES, '', true);
        $activity->addAttendee($organizer);

        $this->entityManager->persist($activity);
        $this->entityManager->persist($organizer);
        $this->entityManager->flush();

        return $activity;
    }

    public function update(Activity $activity, ActivityDto $data): void
    {
        $data->toEntity($activity);
        $this->entityManager->flush();
    }

    /**
     * Saves the member's answer (yes, maybe or no) and comment; adds the member as attendee if needed.
     */
    public function answer(Activity $activity, Member $member, int $status, string $comment): void
    {
        $attendee = $activity->getAttendeeFor($member);
        if (null === $attendee) {
            $attendee = new ActivityAttendee($activity, $member, $status, $comment);
            $activity->addAttendee($attendee);
            $this->entityManager->persist($attendee);
        } else {
            $attendee->setStatus($status);
            $attendee->setComment($comment);
        }
        $this->entityManager->flush();
    }

    /**
     * Removes the member from the activity. Organizers stay: someone has to look after it.
     */
    public function leave(Activity $activity, Member $member): bool
    {
        $attendee = $activity->getAttendeeFor($member);
        if (null === $attendee || $activity->isOrganizer($member)) {
            return false;
        }
        $activity->removeAttendee($attendee);
        $this->entityManager->remove($attendee);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Cancels the activity. As on the legacy pages every answer is set to cancelled; reactivating the
     * activity sets them all back to yes (the original answers are not kept).
     */
    public function cancel(Activity $activity): void
    {
        $activity->setStatus(Activity::STATUS_CANCELLED);
        foreach ($activity->getAttendees() as $attendee) {
            $attendee->setStatus(ActivityAttendee::ACTIVITY_CANCELLED);
        }
        $this->entityManager->flush();
    }

    public function reactivate(Activity $activity): void
    {
        $activity->setStatus(Activity::STATUS_ACTIVE);
        foreach ($activity->getAttendees() as $attendee) {
            $attendee->setStatus(ActivityAttendee::ATTENDS_YES);
        }
        $this->entityManager->flush();
    }

    /**
     * The attendees with an active account, grouped: organizers, then yes, maybe and no.
     *
     * @return array{organizers: ActivityAttendee[], yes: ActivityAttendee[], maybe: ActivityAttendee[], no: ActivityAttendee[]}
     */
    public function getAttendeesByAnswer(Activity $activity): array
    {
        $groups = ['organizers' => [], 'yes' => [], 'maybe' => [], 'no' => []];
        $keys = [
            ActivityAttendee::ATTENDS_YES => 'yes',
            ActivityAttendee::ATTENDS_MAYBE => 'maybe',
            ActivityAttendee::ATTENDS_NO => 'no',
        ];
        foreach ($activity->getAttendees() as $attendee) {
            if (!\in_array($attendee->getAttendee()->getStatus(), MemberStatusType::ACTIVE_SEARCH_ARRAY, true)) {
                continue;
            }
            if (1 === (int) $attendee->getOrganizer()) {
                $groups['organizers'][] = $attendee;
            }
            $key = $keys[(int) $attendee->getStatus()] ?? null;
            if (null !== $key) {
                $groups[$key][] = $attendee;
            }
        }
        foreach ($groups as &$group) {
            usort($group, static fn (ActivityAttendee $a, ActivityAttendee $b) => strcasecmp(
                $a->getAttendee()->getUsername(),
                $b->getAttendee()->getUsername()
            ));
        }

        return $groups;
    }

    public function getRadius(Member $member): int
    {
        $radius = (int) $member->getMemberPreference($this->getRadiusPreference())->getValue();

        return \in_array($radius, ActivityRadiusType::DISTANCES, true) ? $radius : 25;
    }

    public function setRadius(Member $member, int $radius): void
    {
        $memberPreference = $member->getMemberPreference($this->getRadiusPreference());
        $memberPreference->setValue((string) $radius);
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();
    }

    private function getRadiusPreference(): Preference
    {
        /** @var Preference $preference */
        $preference = $this->entityManager->getRepository(Preference::class)
            ->findOneBy(['codename' => Preference::ACTIVITIES_NEAR_ME_RADIUS]);

        return $preference;
    }
}
