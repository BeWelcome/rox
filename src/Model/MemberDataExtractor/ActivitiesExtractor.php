<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\ActivityAttendee;
use App\Entity\Member;
use App\Repository\ActivityAttendeeRepository;

final class ActivitiesExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $activities = [];
        /** @var ActivityAttendeeRepository $attendeeRepository */
        $attendeeRepository = $this->getRepository(ActivityAttendee::class);
        /** @var ActivityAttendee[] $activities */
        $activitiesOfMember = $attendeeRepository->findActivitiesOfMember($member);
        if (!empty($activitiesOfMember)) {
            /** @var ActivityAttendee $attendee */
            $i = 1;
            foreach ($activitiesOfMember as $attendee) {
                $this->writePersonalDataFileSubDirectory(
                    [
                        'activity' => $attendee->getActivity(),
                        'organizer' => $attendee->getOrganizer(),
                        'status' => $attendee->getStatus(),
                        'comment' => $attendee->getComment(),
                    ],
                    'activity',
                    $tempDir . 'activities',
                    'activity-' . $i
                );
                $activities[$i] = $attendee->getActivity();
                ++$i;
            }
        }

        return $this->writePersonalDataFile(['activities' => $activities], 'activities');
    }
}
