<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\NewLocation;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use Carbon\Carbon;
use DateTime;
use Mockery;

class InvitationUtility
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getThread(Member $host, Member $guest, Subtrip $leg, int $status, int $replies): array
    {
        $subject = Mockery::mock(Subject::class, [
            'getSubject' => 'Subject',
        ]);
        $today = new Carbon();
        $someDaysAhead = new Carbon();
        $someDaysAhead->addDays($replies);
        $request = Mockery::mock(HostingRequest::class, [
            'getId' => 1,
            'getArrival' => $today,
            'getDeparture' => $someDaysAhead,
            'getNumberOfTravellers' => 2,
            'getFlexible' => true,
            'getStatus' => $status,
            'getInviteForLeg' => $leg,
        ]);
        $request->shouldReceive('getStatus')->andReturn($status);

        $parent = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Initial invitation',
        ]);
        $parent->shouldReceive('getSubject')->andReturn($subject);
        $parent->shouldReceive('getCreated')->andReturn(new Carbon());
        $parent->shouldReceive('getSender')->andReturn($host);
        $parent->shouldReceive('getInitiator')->andReturn($host);
        $parent->shouldReceive('getReceiver')->andReturn($guest);
        $parent->shouldReceive('getRequest')->andReturn($request);
        $parent->shouldReceive('isDeletedByMember')->andReturn(false);
        $parent->shouldReceive('isPurgedByMember')->andReturn(false);

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

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getReply(
        Message $parent,
        Subject $subject,
        HostingRequest $request,
        Member $guest,
        Member $host,
        int $number
    ): Message {
        $reply = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Reply ' . $number,
            'getParent' => $parent,
        ]);

        $reply->shouldReceive('getSubject')->andReturn($subject);
        $reply->shouldReceive('getCreated')->andReturn(new Carbon());
        $reply->shouldReceive('getSender')->andReturn($guest);
        $reply->shouldReceive('getInitiator')->andReturn($parent->getInitiator());
        $reply->shouldReceive('getReceiver')->andReturn($host);
        $reply->shouldReceive('getRequest')->andReturn($request);
        $reply->shouldReceive('isDeletedByMember')->andReturn(false);
        $reply->shouldReceive('isPurgedByMember')->andReturn(false);

        return $reply;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param mixed $host
     */
    public function getLeg($host): Subtrip
    {
        $trip = Mockery::mock(Trip::class, [
            'getId' => 1,
            'getCreator' => $host,
            'getSummary' => 'Mocking Bird',
            'getDescription' => 'Mocking description',
            'getCountOfTravellers' => 2,
            'getAdditionalInfo' => TripAdditionalInfoType::NONE,
            'getCreated' => new DateTime(),
        ]);
        $location = new NewLocation();
        $location->setName('Mock');
        $leg = Mockery::mock(SubTrip::class, [
            'getId' => 1,
            'getArrival' => Carbon::instance(new DateTime('2021-02-22')),
            'getDeparture' => Carbon::instance(new DateTime('2021-02-24')),
            'getOptions' => [SubtripOptionsType::MEET_LOCALS],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $host,
        ]);

        return $leg;
    }
}
