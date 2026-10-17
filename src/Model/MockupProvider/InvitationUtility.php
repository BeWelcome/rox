<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\NewLocation;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use Carbon\Carbon;
use DateTime;
use ReflectionProperty;

class InvitationUtility
{
    public function getThread(Member $host, Member $guest, ?Subtrip $leg, int $status, int $replies): array
    {
        $subject = (new Subject())->setSubject('Subject');

        $today = new Carbon();
        $someDaysAhead = new Carbon();
        $someDaysAhead->addDays($replies);

        $request = (new HostingRequest())
            ->setArrival($today)
            ->setDeparture($someDaysAhead)
            ->setNumberOfTravellers(2)
            ->setFlexible(true)
            ->setStatus($status)
            ->setInviteForLeg($leg)
        ;

        $parent = $this->getMessage(1, 'Initial invitation', $today, $host, $host, $guest, $request, $subject)
        ;

        $thread = [];
        $thread[] = $parent;
        $lastMessage = $parent;
        for ($i = 1; $i <= $replies; ++$i) {
            $lastMessage = $this->getReply($lastMessage, $subject, $request, $guest, $host, $i);
            $temp = $host;
            $host = $guest;
            $guest = $temp;
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
        int $number
    ): Message {
        $reply = (new Message())
            ->setMessage('Reply ' . $number)
            ->setSubject($subject)
            ->setCreated(new Carbon())
            ->setInitiator($parent->getInitiator())
            ->setSender($guest)
            ->setReceiver($host)
            ->setRequest($request)
            ->setDeleteRequest('')
            ->setParent($parent)
        ;

        $reflectionProperty = new ReflectionProperty(Message::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($reply, $number);

        return $reply;
    }

    public function getLeg($host): Subtrip
    {
        $trip = (new Trip())
            ->setCreator($host)
            ->setSummary('Mocking Bird')
            ->setDescription('Mocking description')
            ->setCountOfTravellers(2)
            ->setAdditionalInfo(TripAdditionalInfoType::NONE)
            ->setCreated(new DateTime())
        ;

        $reflectionProperty = new ReflectionProperty(Trip::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($trip, 1);

        $location = new NewLocation();
        $location->setName('Mock');

        $leg = (new SubTrip())
            ->setArrival(Carbon::instance(new DateTime('2021-02-22')))
            ->setDeparture(Carbon::instance(new DateTime('2021-02-24')))
            ->setLocation($location)
            ->setOptions([SubtripOptionsType::MEET_LOCALS])
            ->setTrip($trip)
            ->setInvitedBy($host)
        ;

        $reflectionProperty = new ReflectionProperty(SubTrip::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($leg, 1);

        return $leg;
    }

    private function getMessage(
        int $id,
        string $message,
        Carbon $today,
        Member $sender,
        Member $initiator,
        Member $receiver,
        HostingRequest $request,
        Subject $subject
    ): Message {
        $message = (new Message())
            ->setMessage($message)
            ->setCreated($today)
            ->setSender($sender)
            ->setInitiator($initiator)
            ->setReceiver($receiver)
            ->setRequest($request)
            ->setSubject($subject)
        ;

        $reflectionProperty = new ReflectionProperty(Message::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $id);

        return $message;
    }
}
