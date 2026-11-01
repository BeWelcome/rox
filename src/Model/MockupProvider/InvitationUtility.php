<?php

namespace App\Model\MockupProvider;

use App\Doctrine\LegOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use Carbon\Carbon;
use DateTime;

class InvitationUtility
{
    use MockupEntities;

    /**
     * The invitation and its replies, the newest first.
     *
     * @return list<Message>
     */
    public function getThread(Member $host, Member $guest, Subtrip $leg, int $status, int $replies): array
    {
        $subject = new Subject()->setSubject('Subject');
        $request = self::withId(new HostingRequest(), 1)
            ->setArrival(new Carbon())
            ->setDeparture(new Carbon()->addDays($replies))
            ->setNumberOfTravellers(2)
            ->setFlexible(true)
            ->setStatus($status)
            ->setInviteForLeg($leg);

        $parent = $this->message(1, 'Initial invitation', $subject, $request, $host, $guest, $host);

        $thread = [$parent];
        $lastMessage = $parent;
        for ($i = 1; $i <= $replies; ++$i) {
            $lastMessage = $this->getReply($lastMessage, $subject, $request, $guest, $host, $i);
            [$host, $guest] = [$guest, $host];
            $thread[] = $lastMessage;
        }

        return array_reverse($thread);
    }

    public function getReply(
        Message $parent,
        Subject $subject,
        HostingRequest $request,
        Member $guest,
        Member $host,
        int $number,
    ): Message {
        return $this->message($number + 1, 'Reply ' . $number, $subject, $request, $guest, $host, $parent->getInitiator())
            ->setParent($parent);
    }

    public function getLeg(Member $host): Subtrip
    {
        $trip = self::withId(new Trip(), 1)
            ->setCreator($host)
            ->setSummary('Mocking Bird')
            ->setDescription('Mocking description')
            ->setCountOfTravellers(2)
            ->setAdditionalInfo(TripAdditionalInfoType::NONE)
            ->setCreated(new DateTime());
        $location = new Location();
        $location->setName('Mock');

        return self::withId(new Subtrip(), 1)
            ->setArrival(new Carbon('2021-02-22'))
            ->setDeparture(new Carbon('2021-02-24'))
            ->setOptions([LegOptionsType::MEET_LOCALS])
            ->setLocation($location)
            ->setTrip($trip)
            ->setInvitedBy($host);
    }

    private function message(
        int $id,
        string $text,
        Subject $subject,
        HostingRequest $request,
        Member $sender,
        Member $receiver,
        Member $initiator,
    ): Message {
        return self::withId(new Message(), $id)
            ->setMessage($text)
            ->setSubject($subject)
            ->setRequest($request)
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setInitiator($initiator)
            ->setCreated(new Carbon());
    }
}
